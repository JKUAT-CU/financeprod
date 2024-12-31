<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Details</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            margin-bottom: 4vh;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .table-container {
            margin-bottom: 20px;
        }
        .table thead {
            background-color: #343a40;
            color: white;
        }
        .table tbody tr:nth-child(even) {
            background-color: #e9ecef;
        }
        .section-header {
            color: #343a40;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .grand-total {
            background-color: #28a745;
            color: white;
            padding: 10px;
            font-size: 1.25rem;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Budget Details</h2>

    <!-- Events Section -->
    <div id="eventsSection" class="table-container">
        <h3 class="section-header">Events</h3>
    </div>

    <!-- Grand Total -->
    <div class="grand-total">
        <strong>Finance Grand Total: </strong> <span id="financeGrandTotal">0.00</span>
    </div>

    <!-- Submit -->
    <div class="mt-3">
        <button class="btn btn-success" id="submitBudgetButton">Submit Suggestions</button>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script>
$(document).ready(function () {
    // Extract the 'budgetId' from the URL query parameters
    const urlParams = new URLSearchParams(window.location.search);
    const budgetId = urlParams.get('budgetId');

    // Stop execution if no budgetId is found
    if (!budgetId) {
        console.error("No budgetId found in the URL");
        return;
    }

    // Fetch budget details from the backend
    fetch(`backend/fetch_budget_details.php?budgetId=${budgetId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                populateEvents(Object.values(data.events || {}));
            }
        })
        .catch(error => console.error('Error fetching data:', error));

    // Function to update totals based on changes in item quantities or costs
    function updateTotals() {
        let grandTotal = 0; // Initialize grand total for all events
        $('.event-group').each(function () {
            let eventTotal = 0; // Initialize event-specific total
            let eventFinanceCost = 0; // Initialize event-specific finance cost
            
            $(this).find('.item-row').each(function () {
                const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
                const costPerItem = parseFloat($(this).find('.item-cost').val()) || 0;
                const financeCost = parseFloat($(this).find('.finance-cost').val()) || 0;
                const totalCost = (quantity * costPerItem) + financeCost;

                $(this).find('.total-cost').text(totalCost.toFixed(2));
                eventTotal += totalCost;
                eventFinanceCost += financeCost;
            });

            // Update event subtotal and finance cost
            $(this).find('.subtotal-total-cost').text(eventTotal.toFixed(2));
            $(this).find('.subtotal-finance-cost').text(eventFinanceCost.toFixed(2));

            grandTotal += eventFinanceCost; // Add event finance cost to grand total
        });

        // Update the grand total for all events
        $('#financeGrandTotal').text(grandTotal.toFixed(2));
    }

    // Function to populate events from backend data
    function populateEvents(events) {
        const container = $('#eventsSection');
        if (!Array.isArray(events) || events.length === 0) {
            container.append('<p>No events available.</p>');
            return;
        }

        events.forEach(event => {
            let tableHtml = `
                <h5>${event.event_name}</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price Per Item</th>
                            <th>Total Cost</th>
                            <th>FAC</th>
                            <th>Comment</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            // Populate item rows
            event.items.forEach(item => {
                const totalCost = parseFloat(item.quantity * item.cost_per_item).toFixed(2);
                tableHtml += `
                    <tr class="item-row">
                        <td>${item.item_name}</td>
                        <td><input class="item-quantity form-control" type="number" value="${item.quantity}" /></td>
                        <td><input class="item-cost form-control" type="number" value="${item.cost_per_item}" /></td>
                        <td class="total-cost">${totalCost}</td>
                        <td><input class="finance-cost form-control" type="number" value="${item.finance_cost || totalCost}" onchange="updateTotals()" /></td>
                        <td><input class="comment form-control" type="text" value="${item.comment || 'Passed as is'}" /></td>
                    </tr>
                `;
            });

            // Add subtotal row
            tableHtml += `
                <tr class="subtotal-row">
                    <td colspan="3" class="text-right"><strong>Subtotal</strong></td>
                    <td class="subtotal-total-cost">0.00</td>
                    <td class="subtotal-finance-cost">0.00</td>
                    <td></td>
                </tr>
            `;

            tableHtml += `</tbody></table>`;

            container.append(`
                <div class="event-group" data-event-name="${event.event_name}">
                    ${tableHtml}
                </div>
            `);
        });

        // Attach input event listener to update totals
        $(document).on('input', '.item-quantity, .item-cost, .finance-cost', updateTotals);
    }

    // Add event listener to submit the budget
    $('#submitBudgetButton').on('click', submitBudget);

    // Function to submit the budget and generate a PDF
    async function submitBudget() {
        try {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();
            const departmentName = "Sample Department";  // You can customize this
            const currentYear = new Date().getFullYear();

            // Fetch letterhead image
            const letterheadImage = await fetch("assets/images/letterhead.gif")
                .then(res => res.ok ? res.blob() : null)
                .then(blob => {
                    if (blob) {
                        return new Promise(resolve => {
                            const reader = new FileReader();
                            reader.onloadend = () => resolve(reader.result);
                            reader.readAsDataURL(blob);
                        });
                    }
                    return null;
                })
                .catch(() => null);

            // Add department and year to PDF
            pdf.setFontSize(16);
            pdf.text(`${departmentName} Budget for the Year ${currentYear}`, 10, 50);
            pdf.setFontSize(10);
            pdf.text(`Date: ${new Date().toLocaleDateString()}`, 10, 60);

            if (letterheadImage) {
                pdf.addImage(letterheadImage, "GIF", 10, 10, 190, 30);
            }

            const eventGroups = [];
            let grandTotal = 0;
            let financeTotal = 0;

            // Collect event data for the PDF
            $('#eventsSection .event-group').each(function () {
                const eventName = $(this).data('event-name') || "Unnamed Event";
                const items = [];
                $(this).find('.item-row').each(function () {
                    const itemName = $(this).find('td:first').text() || "Unnamed Item"; // Correcting item name retrieval
                    const quantity = parseInt($(this).find('.item-quantity').val()) || 0;
                    const costPerItem = parseFloat($(this).find('.item-cost').val()) || 0;
                    const totalCost = quantity * costPerItem;
                    const financeCost = parseFloat($(this).find('.finance-cost').val()) || 0;
                    const comment = $(this).find('.comment').val() || "";

                    items.push({
                        item_name: itemName, 
                        quantity, 
                        cost_per_item: costPerItem, 
                        total_cost: totalCost, 
                        finance_cost: financeCost, 
                        comment
                    });
                    financeTotal += financeCost;
                });

                const subtotal = items.reduce((sum, item) => sum + item.total_cost, 0);
                grandTotal += subtotal;

                eventGroups.push({ event_name: eventName, items, subtotal });
            });

            // Generate event details table in PDF
            let currentY = 70;
            eventGroups.forEach(event => {
                pdf.setFontSize(12);
                pdf.text(`Event: ${event.event_name}`, 10, currentY);

                const eventTableData = event.items.map(item => [
                    item.item_name,
                    item.quantity,
                    item.cost_per_item.toFixed(2),
                    item.total_cost.toFixed(2),
                    item.finance_cost.toFixed(2),
                    item.comment
                ]);

                pdf.autoTable({
                    head: [['Item', 'Quantity', 'Cost/Item', 'Total Cost', 'FAC', 'Comment']],
                    body: eventTableData,
                    startY: currentY + 10,
                    theme: 'grid',
                    headStyles: { fillColor: [128, 0, 0] },
                    bodyStyles: { textColor: [0, 0, 0] },
                    alternateRowStyles: { fillColor: [245, 245, 245] },
                });

                currentY = pdf.lastAutoTable.finalY + 10;
                pdf.setFontSize(12);
                pdf.text(`Subtotal: ${event.subtotal.toFixed(2)}`, 10, currentY);
                currentY += 10;
            });

            // Add finance totals to the PDF
            pdf.setFontSize(12);
            pdf.text(`Finance Total: ${financeTotal.toFixed(2)}`, 10, currentY + 10);

            // Add a step down for the Grand Total
            currentY = pdf.lastAutoTable.finalY + 10;
            pdf.text(`Grand Total: ${grandTotal.toFixed(2)}`, 10, currentY);

            // Save the PDF
            pdf.save("budget.pdf");

        } catch (error) {
            console.error("Error generating PDF:", error);
        }
    }
});
</script>

</body>
</html>
