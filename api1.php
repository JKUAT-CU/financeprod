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

$response = [
    'billRefs' => $billRefs,
    'data' => $data
];

header('Content-Type: application/json');
echo json_encode($response);
?>
