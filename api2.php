<?php
include "db.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Query to fetch monthly transaction data
$query = "SELECT 
            SUBSTRING(TransTime, 1, 6) AS month,
            SUM(TransAmount) AS total_amount,
            COALESCE(SUM(Amount), 0) AS budget_amount
          FROM 
            accounts
          LEFT JOIN 
            budgets ON SUBSTRING(TransTime, 1, 6) = Date
          GROUP BY 
            SUBSTRING(TransTime, 1, 6)";

// Execute the query
$result = $conn->query($query);

// Initialize monthly data array
$monthlyData = [];

// Check if query execution was successful
if ($result) {
    // Fetch the monthly transaction results and store them in an array
    while ($row = $result->fetch_assoc()) {
        // Parse total_amount and budget_amount as floats
        $row['total_amount'] = floatval($row['total_amount']);
        $row['budget_amount'] = floatval($row['budget_amount']);
        $monthlyData[] = $row;
    }
} else {
    // Handle query execution error
    die("Query execution error: " . $conn->error);
}

// Output the JSON data
echo json_encode($monthlyData);

// Close the database connection
$conn->close();
?>
