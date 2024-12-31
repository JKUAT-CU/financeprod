<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db.php';

// Query to fetch department and budget details, including the status column
$sql = "SELECT 
            d.id AS department_id, 
            d.name AS department_name, 
            b.id AS budget_id, 
            b.total_amount, 
            b.status 
        FROM departments d 
        JOIN budgets b ON d.id = b.department_id";

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(["error" => "Failed to fetch budgets: " . $mysqli->error]);
    exit;
}

$budgets = [];
while ($row = $result->fetch_assoc()) {
    $budgets[] = $row; // Add each result row to the budgets array
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($budgets);
