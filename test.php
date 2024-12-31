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

            if (!budgetId) {
                console.error("No budgetId found in the URL");
                return;
            }

            fetch(`backend/fetch_budget_details?budgetId=${budgetId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        populateEvents(Object.values(data.events || {}));
                        populateAssets(Object.values(data.assets || {}));
                    }
                })
                .catch(error => console.error('Error fetching data:', error));

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

                $('.asset-group').each(function () {
                    let assetTotalCost = 0;
                    let assetFinanceCost = 0;

                    $(this).find('.item-row').each(function () {
                        const quantity = parseFloat($(this).find('.item-quantity').text()) || 0;
                        const costPerItem = parseFloat($(this).find('.item-cost').text()) || 0;
                        const totalCost = quantity * costPerItem;
                        const financeCost = parseFloat($(this).find('.finance-cost').val()) || 0;

                        $(this).find('.total-cost').text(totalCost.toFixed(2));

                        assetTotalCost += totalCost;
                        assetFinanceCost += financeCost;
                    });

                    $(this).find('.subtotal-total-cost').text(assetTotalCost.toFixed(2));
                    $(this).find('.subtotal-finance-cost').text(assetFinanceCost.toFixed(2));

                    grandTotalCost += assetTotalCost;
                    grandFinanceCost += assetFinanceCost;
                });

                $('#totalCostGrandTotal').text(grandTotalCost.toFixed(2));
                $('#financeGrandTotal').text(grandFinanceCost.toFixed(2));
            }

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

                    event.items.forEach(item => {
                        const totalCost = parseFloat(item.quantity * item.cost_per_item).toFixed(2);
                        tableHtml += `
                            <tr class="item-row">
                                <td>${item.item_name}</td>
                                <td class="item-quantity">${item.quantity}</td>
                                <td class="item-cost">${item.cost_per_item}</td>
                                <td class="total-cost">${totalCost}</td>
                                <td><input class="finance-cost form-control" type="number" value="${item.finance_cost || totalCost}" /></td>
                                <td><input class="comment form-control" type="text" value="${item.comment || 'Passed as is'}" /></td>
                            </tr>
                        `;
                    });

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

                $(document).on('input', '.finance-cost', updateTotals);
            }

            function populateAssets(assets) {
                const container = $('#assetsSection');
                if (!Array.isArray(assets) || assets.length === 0) {
                    container.append('<p>No assets available.</p>');
                    return;
                }

                assets.forEach(asset => {
                    let tableHtml = `
                        <h5>${asset.asset_name}</h5>
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
                    asset.items.forEach(item => {
                        const totalCost = parseFloat(item.quantity * item.cost_per_item).toFixed(2);
                        tableHtml += `
                            <tr class="item-row">
                                <td>${item.item_name}</td>
                                <td class="item-quantity">${item.quantity}</td>
                                <td class="item-cost">${item.cost_per_item}</td>
                                <td class="total-cost">${totalCost}</td>
                                <td><input class="finance-cost form-control" type="number" value="${item.finance_cost || totalCost}" /></td>
                                <td><input class="comment form-control" type="text" value="${item.comment || 'Passed as is'}" /></td>
                            </tr>
                        `;
                    });

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
                        <div class="asset-group" data-asset-name="${asset.asset_name}">
                            ${tableHtml}
                        </div>
                    `);
                });

                $(document).on('input', '.finance-cost', updateTotals);
            }

            $('#submitBudgetButton').click(function () {
                const updatedBudgetData = {
                    budgetId: budgetId,
                    events: gatherEventData(),
                    assets: gatherAssetData()
                };

                fetch('backend/update_budget', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updatedBudgetData)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Budget suggestions submitted successfully!');
                        } else {
                            alert('Error submitting budget suggestions.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });

            function gatherEventData() {
                const eventsData = [];
                $('.event-group').each(function () {
                    const eventName = $(this).data('event-name');
                    const items = [];

                    $(this).find('.item-row').each(function () {
                        const item = {
                            item_name: $(this).find('td:first').text(),
                            quantity: parseFloat($(this).find('.item-quantity').text()) || 0,
                            cost_per_item: parseFloat($(this).find('.item-cost').text()) || 0,
                            total_cost: parseFloat($(this).find('.total-cost').text()) || 0,
                            finance_cost: parseFloat($(this).find('.finance-cost').val()) || 0,
                            comment: $(this).find('.comment').val()
                        };

                        items.push(item);
                    });

                    eventsData.push({ event_name: eventName, items: items });
                });
                return eventsData;
            }

            function gatherAssetData() {
                const assetsData = [];
                $('.asset-group').each(function () {
                    const assetName = $(this).data('asset-name');
                    const items = [];

                    $(this).find('.item-row').each(function () {
                        const item = {
                            item_name: $(this).find('td:first').text(),
                            quantity: parseFloat($(this).find('.item-quantity').text()) || 0,
                            cost_per_item: parseFloat($(this).find('.item-cost').text()) || 0,
                            total_cost: parseFloat($(this).find('.total-cost').text()) || 0,
                            finance_cost: parseFloat($(this).find('.finance-cost').val()) || 0,
                            comment: $(this).find('.comment').val()
                        };

                        items.push(item);
                    });

                    assetsData.push({ asset_name: assetName, items: items });
                });
                return assetsData;
            }
        });
    </script>
</body>

</html>
