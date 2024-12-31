<?php
require_once 'session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Finance Dashboard</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Department</th>
                <th>Budget Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="departments-list">
            <!-- Rows dynamically added -->
        </tbody>
    </table>
</div>

<script>
    // Fetch and display the list of departments and budgets
    async function fetchBudgets() {
        try {
            const response = await fetch('backend/fetch_budgets.php');
            const budgets = await response.json();
            const tableBody = document.getElementById('departments-list');
            tableBody.innerHTML = ''; // Clear existing rows

            budgets.forEach(budget => {
                // Determine the status badge and color
                let statusBadge = '';
                switch (budget.status) {
                    case 'Pending':
                        statusBadge = `<span class="badge badge-warning">Pending</span>`;
                        break;
                    case 'Finance Approved':
                        statusBadge = `<span class="badge badge-success">Finance Approved</span>`;
                        break;
                    case 'Exec Approved':
                        statusBadge = `<span class="badge badge-primary">Exec Approved</span>`;
                        break;
                    default:
                        statusBadge = `<span class="badge badge-secondary">Unknown</span>`;
                        break;
                }

                // Add a row for each budget
                const row = `
                    <tr>
                        <td>${budget.department_name}</td>
                        <td>${budget.total_amount}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <!-- Redirect to editbudgets.php with budgetId as query parameter -->
                            <button class="btn btn-primary" onclick="viewBudget(${budget.budget_id})">View Details</button>
                        </td>
                    </tr>`;
                tableBody.innerHTML += row;
            });
        } catch (error) {
            console.error("Error fetching budgets:", error);
        }
    }

    // Redirect to the editbudgets page when a budget is selected
    function viewBudget(budgetId) {
        // Open the editbudgets page in a new tab/window with the budgetId parameter
        window.open(`editbudgets?budgetId=${budgetId}`, '_blank');
    }

    // Call fetchBudgets() when the page loads to populate the budget list
    document.addEventListener('DOMContentLoaded', fetchBudgets);
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
