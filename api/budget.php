<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.3/js/jquery.tablesorter.min.js"></script>
    <style>
        .department-group {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Finance Dashboard</h2>
    <table class="table table-striped table-sorter">
        <thead>
            <tr>
                <th data-sorter="text">Department</th>
                <th data-sorter="digit">Semester</th>
                <th data-sorter="digit">Budget Amount</th>
                <th data-sorter="text">Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="departments-list">
            <!-- Rows dynamically added -->
        </tbody>
    </table>
</div>

<script>
    // Fetch and display budgets grouped by department
    async function fetchBudgets() {
        try {
            const response = await fetch('backend/fetch_budgets.php');
            const budgets = await response.json();

            // Group budgets by department
            const groupedBudgets = budgets.reduce((acc, budget) => {
                const { department_name, grand_total } = budget;
                if (!acc[department_name]) {
                    acc[department_name] = { budgets: [], total: 0 };
                }
                acc[department_name].budgets.push(budget);
                acc[department_name].total += parseFloat(grand_total);
                return acc;
            }, {});

            // Build table rows
            const tableBody = document.getElementById('departments-list');
            tableBody.innerHTML = '';

            Object.entries(groupedBudgets).forEach(([department, data]) => {
                // Department header
                tableBody.innerHTML += `
                    <tr class="department-group">
                        <td colspan="5">${department}</td>
                    </tr>
                `;

                // Semester rows
                data.budgets.forEach(budget => {
                    const statusBadge = getStatusBadge(budget.status);
                    tableBody.innerHTML += `
                        <tr>
                            <td>${budget.department_name}</td>
                            <td>${budget.semester}</td>
                            <td>${parseFloat(budget.grand_total).toFixed(2)}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-primary" onclick="viewBudget(${budget.budget_id})">View Details</button>
                            </td>
                        </tr>
                    `;
                });

                // Total row for the department
                tableBody.innerHTML += `
                    <tr class="total-row">
                        <td colspan="2">Total for ${department}</td>
                        <td>${data.total.toFixed(2)}</td>
                        <td colspan="2"></td>
                    </tr>
                `;
            });

            // Apply tablesorter plugin
            $(".table-sorter").tablesorter({
                sortList: [[0, 0]], // Default sort by department
                widgets: ["zebra"]
            });

        } catch (error) {
            console.error("Error fetching budgets:", error);
        }
    }

    // Determine status badge
    function getStatusBadge(status) {
        switch (status) {
            case 'Pending':
                return `<span class="badge badge-warning">Pending</span>`;
            case 'Finance Approved':
                return `<span class="badge badge-success">Finance Approved</span>`;
            case 'Exec Approved':
                return `<span class="badge badge-primary">Exec Approved</span>`;
            default:
                return `<span class="badge badge-secondary">Unknown</span>`;
        }
    }

    // Redirect to editbudgets page
    function viewBudget(budgetId) {
        window.open(`editbudgets?budgetId=${budgetId}`, '_blank');
    }

    // Fetch budgets on page load
    document.addEventListener('DOMContentLoaded', fetchBudgets);
</script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
