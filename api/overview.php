<?php
include "navbar.php";
include "sidebar.php";
include "styles.php";
include "scripts.php";
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Title</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<!-- Tailwind CSS -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.1.2/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-2.0.7/af-2.7.0/datatables.min.css" rel="stylesheet">

<style>
    .table-container {
        overflow-x: auto;
    }
    .export{
        padding: 1vh;
        text-align: right;
    }
</style>


<body>

   <div class="table-container";>
    <div class="button-class" style="position:inherit";>
        <button class="btn btn-primary" onclick="showView('years')">Yearly View</button>
        <button class="btn btn-primary" onclick="showView('months')">Monthly View</button>
        <button class="btn btn-primary" onclick="showView('semesters')">Semester View</button>
        <button class="btn btn-primary" onclick="showView('weeks')">Weekly View</button>
    </div>

        <div id="years" class="view">
            <h2 class="text-xl font-semibold mb-2">Yearly View</h2>
            <div class="export">
            <button class="btn btn-primary" onclick="exportToExcel('yearsTable')">Export to Excel</button>
            </div>
            <div id="yearsTable"></div>
        </div>

        <div id="months" class="view">
            <h2 class="text-xl font-semibold mb-2">Monthly View</h2>
            <div class="export">
            <button class="btn btn-primary" onclick="exportToExcel('monthsTable')">Export to Excel</button>
            </div>
            <div id="monthsTable"></div>
        </div>

        <div id="semesters" class="view">
            <h2 class="text-xl font-semibold mb-2">Semester View</h2>
            <div class="export">
            <button class="btn btn-primary" onclick="exportToExcel('semesterTable')">Export to Excel</button>
            </div>
            <div id="semestersTable"></div>
        </div>

        <div id="weeks" class="view">
            <h2 class="text-xl font-semibold mb-2">Weekly View</h2>
            <div class="export">
            <button class="btn btn-primary" onclick="exportToExcel('weeksTable')">Export to Excel</button>
            </div>
            <div id="weeksTable"></div>
        </div>
    </div>
               

    <script src="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-2.0.7/af-2.7.0/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

 <script src="js/charts/income.js"></script>
    <script>
        function exportToExcel(tableId) {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table);
        XLSX.writeFile(wb, 'data.xlsx');
    }

        function showView(view) {
            var views = document.getElementsByClassName('view');
            for (var i = 0; i < views.length; i++) {
                views[i].style.display = 'none';
            }
            document.getElementById(view).style.display = 'block';
        }



        function renderTable(elementId, timeframe, data, billRefs) {
            let tableHtml = '<table class="table table-bordered">';
            tableHtml += '<thead><tr><th>' + timeframe + '</th>';
            billRefs.forEach(billRef => {
                tableHtml += '<th>' + billRef + '</th>';
            });
            tableHtml += '<th>Total</th></tr></thead><tbody>';

            for (const [period, amounts] of Object.entries(data)) {
                let rowTotal = 0;
                tableHtml += '<tr><td>' + period + '</td>';
                billRefs.forEach(billRef => {
                    const amount = amounts[billRef] ? amounts[billRef] : 0;
                    tableHtml += '<td>' + amount + '</td>';
                    rowTotal += amount;
                });
                tableHtml += '<td>' + rowTotal + '</td></tr>';
            }

            tableHtml += '</tbody></table>';
            document.getElementById(elementId).innerHTML = tableHtml;
        }

        // Fetch data and render tables on page load
        fetchData();
    </script>
</body>

</html>
