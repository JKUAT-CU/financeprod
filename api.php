<?php
include "db.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Calculate the date range for the last 12 months
$start_date = date('Y-m-d', strtotime('-12 months'));
$end_date = date('Y-m-d');

// Function to fetch yearly collection data
function getYearlyCollection($conn) {
    $sql = "SELECT SUM(TransAmount) AS yearlyCollection FROM accounts WHERE YEAR(TransTime) = YEAR(CURDATE())";
    $result = $conn->query($sql);

    // Check if query execution was successful
    if ($result) {
        $row = $result->fetch_assoc();
        // Convert null values to zero
        $yearlyCollection = $row["yearlyCollection"] ?? 0;
    } else {
        // Handle query execution error
        die("Query execution error: " . $conn->error);
    }
    return $yearlyCollection;
}

// Function to fetch yearly expense data
function getYearlyExpense($conn) {
    $sql = "SELECT SUM(CASE WHEN Amount < 0 THEN Amount ELSE 0 END) AS yearlyExpense FROM Expenses";
    $result = $conn->query($sql);

    // Check if query execution was successful
    if ($result) {
        $row = $result->fetch_assoc();
        // Convert null values to zero
        $yearlyExpense = abs($row["yearlyExpense"]) ?? 0;
    } else {
        // Handle query execution error
        die("Query execution error: " . $conn->error);
    }
    return $yearlyExpense;
}

// Function to fetch semester collection data
function getSemesterCollection($conn) {
    $current_month = date('m');
    if ($current_month >= 9 && $current_month <= 12) {
        $start_date = date('Y') . '0901';
        $end_date = date('Y') . '1231';
    } elseif ($current_month >= 1 && $current_month <= 4) {
        $start_date = date('Y') . '0101';
        $end_date = date('Y') . '0430';
    } else {
        $start_date = date('Y') . '0501';
        $end_date = date('Y') . '0831';
    }
    $sql = "SELECT SUM(TransAmount) AS semesterCollection FROM accounts WHERE TransTime BETWEEN '$start_date' AND '$end_date'";
    $result = $conn->query($sql);

    // Check if query execution was successful
    if ($result) {
        $row = $result->fetch_assoc();
        // Convert null values to zero
        $semesterCollection = $row["semesterCollection"] ?? 0;
    } else {
        // Handle query execution error
        die("Query execution error: " . $conn->error);
    }
    return $semesterCollection;
}

// Function to fetch semester expense data
function getSemesterExpense($conn) {
    $current_month = date('m');
    if ($current_month >= 9 && $current_month <= 12) {
        $start_date = date('Y') . '0901';
        $end_date = date('Y') . '1231';
    } elseif ($current_month >= 1 && $current_month <= 4) {
        $start_date = date('Y') . '0101';
        $end_date = date('Y') . '0430';
    } else {
        $start_date = date('Y') . '0501';
        $end_date = date('Y') . '0831';
    }
    $sql = "SELECT SUM(Amount) AS semesterExpense FROM Expenses WHERE TransactionDate BETWEEN '$start_date' AND '$end_date'";
    $result = $conn->query($sql);

    // Check if query execution was successful
    if ($result) {
        $row = $result->fetch_assoc();
        // Convert null values to zero
        $semesterExpense = $row["semesterExpense"] ?? 0;
    } else {
        // Handle query execution error
        die("Query execution error: " . $conn->error);
    }
    return $semesterExpense;
}

// Query to fetch monthly transaction data
$query = "SELECT 
            YEAR(`TransTime`) AS `year`, 
            MONTHNAME(STR_TO_DATE(SUBSTRING(`TransTime`, 1, 8), '%Y%m%d%H')) AS `month`, 
            COALESCE(SUM(`TransAmount`), 0) AS `total_amount` 
          FROM 
            `accounts` 
          WHERE 
            STR_TO_DATE(SUBSTRING(`TransTime`, 1, 8), '%Y%m%d%H') BETWEEN '$start_date' AND '$end_date'
          GROUP BY 
            YEAR(`TransTime`), 
            MONTHNAME(STR_TO_DATE(SUBSTRING(`TransTime`, 1, 8), '%Y%m%d%H'))";

// Execute the query
$result = $conn->query($query);

// Initialize monthly data array
$monthlyData = [];

// Check if query execution was successful
if ($result) {
    // Fetch the monthly transaction results and store them in an array
    while ($row = $result->fetch_assoc()) {
        // Parse total_amount as a float
        $row['total_amount'] = floatval($row['total_amount']);
        $monthlyData[] = $row;
    }
} else {
    // Handle query execution error
    die("Query execution error: " . $conn->error);
}

// Fetching data for each category
$response = array(
    "monthlyData" => $monthlyData,
    "yearlyCollection" => getYearlyCollection($conn),
    "yearlyExpense" => getYearlyExpense($conn),
    "semesterCollection" => getSemesterCollection($conn),
    "semesterExpense" => getSemesterExpense($conn)
);

// Output the JSON data
echo json_encode($response);
// Close the database connection
$conn->close();
?>

