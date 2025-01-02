<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db.php';

// Validate and fetch budgetId
$budgetId = filter_input(INPUT_GET, 'budgetId', FILTER_VALIDATE_INT);
if (!$budgetId) {
    echo json_encode(["error" => "Invalid or missing budgetId parameter"]);
    exit;
}

// Initialize the response structure
$response = [
    'budgetDetails' => null,
    'events' => [],
    'assets' => [],
    'department_name' => null,  // Added field for department name
];

try {
    // Fetch budget details, including the status and department_id
    $stmt = $mysqli->prepare("SELECT * FROM budgets WHERE `id` = ?");
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['budgetDetails'] = $result->fetch_assoc();
    $stmt->close();

    // Check if budget exists
    if (!$response['budgetDetails']) {
        echo json_encode(["error" => "Budget not found"]);
        exit;
    }

    // Fetch department_name from the departments table using the department_id from the budget
    $departmentId = $response['budgetDetails']['department_id'];  // Assuming department_id is in the budget table
    if ($departmentId) {
        $stmt = $mysqli->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $department = $result->fetch_assoc();
        if ($department) {
            $response['department_name'] = $department['name'];
        }
        $stmt->close();
    }

    // Fetch events and their items
    $stmt = $mysqli->prepare("
        SELECT 
            e.id AS event_id, 
            e.event_name, 
            e.attendees, 
            ei.id AS item_id, 
            ei.item_name, 
            ei.quantity, 
            ei.cost_per_item, 
            ei.total_cost
        FROM events e
        LEFT JOIN event_items ei ON e.id = ei.event_id
        WHERE e.budget_id = ?
        ORDER BY e.id
    ");
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $eventId = $row['event_id'];
        if (!isset($response['events'][$eventId])) {
            $response['events'][$eventId] = [
                'event_id' => $row['event_id'],
                'event_name' => $row['event_name'],
                'attendees' => $row['attendees'],
                'items' => [],
            ];
        }
        // Append items for the event
        if ($row['item_id'] !== null) {
            $response['events'][$eventId]['items'][] = [
                'item_id' => $row['item_id'],
                'item_name' => $row['item_name'],
                'quantity' => $row['quantity'],
                'cost_per_item' => $row['cost_per_item'],
                'total_cost' => $row['total_cost'],
            ];
        }
    }
    $stmt->close();

    // Normalize events array
    $response['events'] = array_values($response['events']);

    // Fetch assets
    $stmt = $mysqli->prepare("
        SELECT 
            a.id AS asset_id, 
            a.item_name, 
            a.quantity, 
            a.cost_per_item, 
            a.total_cost
        FROM assets a
        WHERE a.budget_id = ?
    ");
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['assets'] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Pass data to frontend
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(["error" => "An unexpected error occurred"]);
}
