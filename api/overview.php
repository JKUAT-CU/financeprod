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
            <div id="yearsTable"></div>
        </div>

        <div id="months" class="view">
            <h2 class="text-xl font-semibold mb-2">Monthly View</h2>
            <div id="monthsTable"></div>
        </div>

        <div id="semesters" class="view">
            <h2 class="text-xl font-semibold mb-2">Semester View</h2>
            <div id="semestersTable"></div>
        </div>

        <div id="weeks" class="view">
            <h2 class="text-xl font-semibold mb-2">Weekly View</h2>
            <div id="weeksTable"></div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-2.0.7/af-2.7.0/datatables.min.js"></script>
    <script src="js/charts/income.js"></script>
    <script>
        function showView(view) {
            var views = document.getElementsByClassName('view');
            for (var i = 0; i < views.length; i++) {
                views[i].style.display = 'none';
            }
            document.getElementById(view).style.display = 'block';
        }

        function fetchData() {
            fetch('your_php_script.php')
                .then(response => response.json())
                .then(data => {
                    renderTable('yearsTable', 'Year', data.data.years, data.billRefs);
                    renderTable('monthsTable', 'Month', data.data.months, data.billRefs);
                    renderTable('semestersTable', 'Semester', data.data.semesters, data.billRefs);
                    renderTable('weeksTable', 'Week', data.data.weeks, data.billRefs);
                    showView('years');
                });
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
