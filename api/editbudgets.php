<?php
require_once 'session.php';
?>
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

    <!-- Assets Section -->
    <div id="assetsSection" class="table-container">
        <h3 class="section-header">Assets</h3>
    </div>

    <!-- Grand Totals -->
    <div class="grand-total" style="text-align:left; background-color:black; width:20vw">
        <strong>Original Total: </strong><span id="totalCostGrandTotal">0.00</span>
    </div>
    <div class="grand-total" style="text-align:left; background-color:black; width:20vw">
        <strong>FAC Total: </strong><span id="financeGrandTotal">0.00</span>
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
    const urlParams = new URLSearchParams(window.location.search);
    const budgetId = urlParams.get('budgetId');
    let departmentName = "JKUATCU BUDGET";  // Default department name

    if (!budgetId) {
        console.error("No budgetId found in the URL");
        return;
    }

    // Fetch budget details from the backend
    fetch(`backend/fetch_budget_details?budgetId=${budgetId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                populateEvents(Object.values(data.events || {}));
                populateAssets(data.assets || []);
                departmentName = data.department_name || departmentName; // Dynamically assign department name from backend
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            alert('Failed to load budget details.');
        });

    // Function to update totals based on changes in item quantities or costs
    function updateTotals() {
        let grandTotalCost = 0;
        let grandFinanceCost = 0;

        $('.event-group').each(function () {
            let eventTotalCost = 0;
            let eventFinanceCost = 0;

            $(this).find('.item-row').each(function () {
                const quantity = parseFloat($(this).find('.item-quantity').text()) || 0;
                const costPerItem = parseFloat($(this).find('.item-cost').text()) || 0;
                const totalCost = quantity * costPerItem;
                const financeCost = parseFloat($(this).find('.finance-cost').val()) || 0;

                $(this).find('.total-cost').text(totalCost.toFixed(2));

                eventTotalCost += totalCost;
                eventFinanceCost += financeCost;
            });

            $(this).find('.subtotal-total-cost').text(eventTotalCost.toFixed(2));
            $(this).find('.subtotal-finance-cost').text(eventFinanceCost.toFixed(2));

            grandTotalCost += eventTotalCost;
            grandFinanceCost += eventFinanceCost;
        });

        $('#totalCostGrandTotal').text(grandTotalCost.toFixed(2));
        $('#financeGrandTotal').text(grandFinanceCost.toFixed(2));
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
                        <td class="item-quantity">${item.quantity}</td>
                        <td class="item-cost">${item.cost_per_item}</td>
                        <td class="total-cost">${totalCost}</td>
                        <input type="hidden" class="item-quantity-value" value="${item.quantity}" />
                        <input type="hidden" class="item-cost-value" value="${item.cost_per_item}" />
                        <input type="hidden" class="total-cost-value" value="${totalCost}" />
                        <!-- Editable fields -->
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

    // Function to populate assets from backend data
    function populateAssets(assets) {
        const container = $('#assetsSection');
        if (!Array.isArray(assets) || assets.length === 0) {
            container.append('<p>No assets available.</p>');
            return;
        }

        let tableHtml = `
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Asset Name</th>
                        <th>Quantity</th>
                        <th>Cost Per Item</th>
                        <th>Total Cost</th>
                        <th>FAC</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
        `;

        assets.forEach(asset => {
            const totalCost = (parseFloat(asset.quantity) * parseFloat(asset.cost_per_item)).toFixed(2);
            tableHtml += `
                <tr>
                    <td>${asset.asset_name}</td>
                    <td>${asset.quantity}</td>
                    <td>${asset.cost_per_item}</td>
                    <td>${totalCost}</td>
                    <td><input class="finance-cost form-control" type="number" value="${asset.finance_cost || totalCost}" onchange="updateTotals()" /></td>
                    <td><input class="comment form-control" type="text" value="${asset.comment || 'Passed as is'}" /></td>
                </tr>
            `;
        });

        tableHtml += `</tbody></table>`;
        container.append(tableHtml);
    }
    if (!departmentName) {
    container.append('<p>Department Name not available.</p>');
    return;
}

$('#submitBudgetButton').on('click', function () {
    submitBudget(departmentName);  // Pass departmentName when submitting the budget
});

async function submitBudget(departmentName) {
    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();
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

        // Add header
        if (letterheadImage) {
            pdf.addImage(letterheadImage, "GIF", 10, 10, 190, 30);
        }

        pdf.setFontSize(16);
        pdf.text(`${departmentName} Budget for the Year ${currentYear}`, 10, 50);
        pdf.setFontSize(10);
        pdf.text(`Date: ${new Date().toLocaleDateString()}`, 10, 60);

        let currentY = 70;
        let grandTotal = 0;
        let financeTotal = 0;

        // Event Groups
        const eventGroups = [];
        $('#eventsSection .event-group').each(function () {
            const eventName = $(this).data('event-name') || "Unnamed Event";
            const items = [];
            $(this).find('.item-row').each(function () {
                const itemName = $(this).find('td:first').text() || "Unnamed Item";
                const quantity = $(this).find('.item-quantity').text() || 0;
                const costPerItem = $(this).find('.item-cost').text() || 0;
                const totalCost = $(this).find('.total-cost').text() || 0;
                const financeCost = parseFloat($(this).find('.finance-cost').val()) || 0;
                const comment = $(this).find('.comment').val() || "";

                items.push({
                    item_name: itemName,
                    quantity: quantity,
                    cost_per_item: costPerItem,
                    total_cost: totalCost,
                    finance_cost: financeCost,
                    comment: comment
                });

                financeTotal += financeCost;
            });

            const subtotal = items.reduce((sum, item) => sum + parseFloat(item.total_cost || 0), 0);
            grandTotal += subtotal;
            eventGroups.push({ event_name: eventName, items, subtotal });
        });

        // Generate Event Tables
        eventGroups.forEach(event => {
            if (currentY + 30 > pdf.internal.pageSize.height) {
                pdf.addPage();
                currentY = 10;
            }

            pdf.setFontSize(12);
            pdf.text(`Event: ${event.event_name}`, 10, currentY);
            currentY += 10;

            const eventTableData = event.items.map(item => [
                item.item_name,
                item.quantity,
                parseFloat(item.cost_per_item).toFixed(2),
                parseFloat(item.total_cost).toFixed(2),
                parseFloat(item.finance_cost).toFixed(2),
                item.comment
            ]);

            pdf.autoTable({
                head: [['Item', 'Quantity', 'Cost/Item', 'Total Cost', 'FAC', 'Comment']],
                body: eventTableData,
                startY: currentY,
                theme: 'grid',
            });

            currentY = pdf.lastAutoTable.finalY + 10;
            pdf.text(`Subtotal: ${event.subtotal.toFixed(2)}`, 10, currentY);
            currentY += 10;
        });

        // Asset Groups
        const assetGroups = [];
        $('#assetsSection .item-row').each(function () {
            const itemName = $(this).find('td:first').text() || "Unnamed Item";
            const quantity = $(this).find('.item-quantity').text() || 0;
            const costPerItem = $(this).find('.item-cost').text() || 0;
            const financeCost = parseFloat($(this).find('.finance-cost').val()) || 0;
            const comment = $(this).find('.comment').val() || "";

            assetGroups.push({
                asset_name: itemName,
                quantity: quantity,
                cost_per_item: costPerItem,
                total_cost: quantity * costPerItem,
                finance_cost: financeCost,
                comment: comment
            });

            financeTotal += financeCost;
        });

        // Generate Asset Table
        if (currentY + 30 > pdf.internal.pageSize.height) {
            pdf.addPage();
            currentY = 10;
        }
        pdf.setFontSize(12);
        pdf.text("Assets Summary", 10, currentY);

        const assetTableData = assetGroups.map(asset => [
            asset.asset_name,
            asset.quantity,
            parseFloat(asset.cost_per_item).toFixed(2),
            parseFloat(asset.total_cost).toFixed(2),
            parseFloat(asset.finance_cost).toFixed(2),
            asset.comment
        ]);

        pdf.autoTable({
            head: [['Asset Name', 'Quantity', 'Cost/Item', 'Total Cost', 'FAC', 'Comment']],
            body: assetTableData,
            startY: currentY + 10,
            theme: 'grid',
        });

        // Final Summary
        currentY = pdf.lastAutoTable.finalY + 20;
        pdf.text(`Finance Total: ${financeTotal.toFixed(2)}`, 10, currentY);
        pdf.text(`Grand Total: ${grandTotal.toFixed(2)}`, 10, currentY + 10);

        // Save the PDF
        pdf.save(`${departmentName}_budget.pdf`);
    } catch (error) {
        console.error("Error generating PDF:", error);
    }
}
  
});
</script>
</body>
</html>
