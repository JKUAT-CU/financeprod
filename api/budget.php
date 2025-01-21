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
                <th onclick="sortTable(0)">Department</th>
                <th onclick="sortTable(1)">Semester</th>
                <th onclick="sortTable(2)">Budget Amount</th>
                <th onclick="sortTable(3)">Status</th>
            </tr>
        </thead>
        <tbody id="departments-list">
            <!-- Rows dynamically added -->
        </tbody>
        <tfoot id="totals-footer">
            <!-- Totals row -->
        </tfoot>
    </table>
</div>

<script>
    async function fetchBudgets() {
        try {
            const response = await fetch('backend/fetch_budgets.php');
            const budgets = await response.json();

            const groupedData = groupByDepartment(budgets);
            const tableBody = document.getElementById('departments-list');
            const footer = document.getElementById('totals-footer');
            
            tableBody.innerHTML = '';
            footer.innerHTML = '';

            let grandTotal = 0;
            const semesterTotals = {};

            for (const departmentName in groupedData) {
                const rows = groupedData[departmentName];
                let departmentTotal = 0;

                rows.forEach(row => {
                    departmentTotal += parseFloat(row.grand_total);
                    grandTotal += parseFloat(row.grand_total);
                    semesterTotals[row.semester] = 
                        (semesterTotals[row.semester] || 0) + parseFloat(row.grand_total);

                    tableBody.innerHTML += `
                        <tr>
                            <td>${row.department_name}</td>
                            <td>${row.semester}</td>
                            <td>${parseFloat(row.grand_total).toFixed(2)}</td>
                            <td>${row.status}</td>
                        </tr>`;
                });

                tableBody.innerHTML += `
                    <tr class="font-weight-bold text-primary">
                        <td colspan="2">Total for ${departmentName}</td>
                        <td>${departmentTotal.toFixed(2)}</td>
                        <td></td>
                    </tr>`;
            }

            // Add overall totals to footer
            footer.innerHTML = `
                <tr class="table-success font-weight-bold">
                    <td colspan="2">Budget Total</td>
                    <td>${grandTotal.toFixed(2)}</td>
                    <td></td>
                </tr>`;

            // Add semester totals
            for (const semester in semesterTotals) {
                footer.innerHTML += `
                    <tr class="table-info">
                        <td colspan="2">Total for ${semester}</td>
                        <td>${semesterTotals[semester].toFixed(2)}</td>
                        <td></td>
                    </tr>`;
            }

        } catch (error) {
            console.error("Error fetching budgets:", error);
        }
    }

    function groupByDepartment(budgets) {
        return budgets.reduce((acc, budget) => {
            acc[budget.department_name] = acc[budget.department_name] || [];
            acc[budget.department_name].push(budget);
            return acc;
        }, {});
    }

    function sortTable(columnIndex) {
        const table = document.querySelector("table");
        const rows = Array.from(table.rows).slice(1, -1); // Exclude header/footer
        const isNumeric = columnIndex === 2; // Budget Amount column
        const sortedRows = rows.sort((a, b) => {
            const cellA = a.cells[columnIndex].textContent.trim();
            const cellB = b.cells[columnIndex].textContent.trim();
            return isNumeric
                ? parseFloat(cellA) - parseFloat(cellB)
                : cellA.localeCompare(cellB);
        });

        const tableBody = document.getElementById('departments-list');
        tableBody.innerHTML = '';
        sortedRows.forEach(row => tableBody.appendChild(row));
    }

    document.addEventListener('DOMContentLoaded', fetchBudgets);
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
