<?php
// Include the database connection code
include 'db.php';

// Fetch data from the accounts table
$sql = "SELECT TransTime, TransAmount, BillRefNumber FROM accounts";
$result = $conn->query($sql);

$data = [];
$billRefs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $transTime = $row['TransTime'];
        $year = substr($transTime, 0, 4);
        $month = substr($transTime, 4, 2);
        $day = substr($transTime, 6, 2);
        $week = date('W', strtotime("$year-$month-$day"));
        $semester = ceil($month / 4);
        
        $billRef = $row['BillRefNumber'];
        $amount = (float)$row['TransAmount'];
        
        // Collect unique BillRefNumbers
        if (!in_array($billRef, $billRefs)) {
            $billRefs[] = $billRef;
        }
        
        // Yearly data
        if (!isset($data['years'][$year][$billRef])) {
            $data['years'][$year][$billRef] = 0;
        }
        $data['years'][$year][$billRef] += $amount;
        
        // Monthly data
        $monthName = date('F', mktime(0, 0, 0, $month, 10)); // Convert month number to name
        $yearMonth = "$year-$monthName";
        if (!isset($data['months'][$yearMonth][$billRef])) {
            $data['months'][$yearMonth][$billRef] = 0;
        }
        $data['months'][$yearMonth][$billRef] += $amount;
        
        // Semester data
        $semesterStart = ($semester == 1) ? 'January' : (($semester == 2) ? 'May' : (($semester == 3) ? 'September' : 'January'));
        $semesterEnd = ($semester == 1) ? 'April' : (($semester == 2) ? 'August' : 'December');
        $yearSemester = "$year-$semesterStart to $semesterEnd";
        if (!isset($data['semesters'][$yearSemester][$billRef])) {
            $data['semesters'][$yearSemester][$billRef] = 0;
        }
        $data['semesters'][$yearSemester][$billRef] += $amount;
        
        // Weekly data
        $startOfWeek = new DateTime();
        $startOfWeek->setISODate($year, $week);
        $endOfWeek = clone $startOfWeek;
        $endOfWeek->add(new DateInterval('P6D'));
        $yearWeek = $startOfWeek->format('Y-m-d') . ' to ' . $endOfWeek->format('Y-m-d');
        if (!isset($data['weeks'][$yearWeek][$billRef])) {
            $data['weeks'][$yearWeek][$billRef] = 0;
        }
        $data['weeks'][$yearWeek][$billRef] += $amount;
    }
}

// Function to render table
function renderTable($timeframe, $data, $billRefs) {
    echo "<table>";
    echo "<tr><th>$timeframe</th>";
    foreach ($billRefs as $billRef) {
        echo "<th>$billRef</th>";
    }
    echo "<th>Total</th></tr>";
    foreach ($data as $period => $amounts) {
        echo "<tr><td>$period</td>";
        $rowTotal = 0;
        foreach ($billRefs as $billRef) {
            $amount = isset($amounts[$billRef]) ? $amounts[$billRef] : 0;
            echo "<td>$amount</td>";
            $rowTotal += $amount;
        }
        echo "<td>$rowTotal</td></tr>";
    }
    echo "</table>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>BillRefNumber Totals</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .view {
            display: none;
        }
    </style>
    <script>
        function showView(view) {
            var views = document.getElementsByClassName('view');
            for (var i = 0; i < views.length; i++) {
                views[i].style.display = 'none';
            }
            document.getElementById(view).style.display = 'block';
        }
    </script>
</head>
<body>

<h1>BillRefNumber Totals</h1>
<button onclick="showView('years')">Yearly View</button>
<button onclick="showView('months')">Monthly View</button>
<button onclick="showView('semesters')">Semester View</button>
<button onclick="showView('weeks')">Weekly View</button>

<div id="years" class="view">
    <h2>Yearly View</h2>
    <?php renderTable('Year', $data['years'], $billRefs); ?>
</div>

<div id="months" class="view">
    <h2>Monthly View</h2>
    <?php renderTable('Month', $data['months'], $billRefs); ?>
</div>

<div id="semesters" class="view">
    <h2>Semester View</h2>
    <?php renderTable('Semester', $data['semesters'], $billRefs); ?>
</div>

<div id="weeks" class="view">
    <h2>Weekly View</h2>
    <?php renderTable('Week', $data['weeks'], $billRefs); ?>
</div>

<script>
    // Show yearly view by default
    showView('years');
</script>

</body>
</html>
