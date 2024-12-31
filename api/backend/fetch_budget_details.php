<?php
// Database connection
$host = 'localhost';
$user = 'jkuatcu_devs';
$password = '#God@isAble!#';  // Ensure this is the correct password
$database = 'jkuatcu_admin';

// Create connection
$mysqli = new mysqli($host, $user, $password, $database);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Enable error reporting for debugging
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
    'department_name' => null,
];

try {
    // Fetch budget details
    $stmt = $mysqli->prepare("SELECT id, department_id, name, status FROM budgets WHERE id = ?");
    if (!$stmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $stmt->bind_result($id, $department_id, $name, $status);

    if ($stmt->fetch()) {
        $response['budgetDetails'] = [
            'id' => $id,
            'department_id' => $department_id,
            'name' => $name,
            'status' => $status,
        ];
    }
    $stmt->close();

    // Check if budget exists
    if (!$response['budgetDetails']) {
        echo json_encode(["error" => "Budget not found"]);
        exit;
    }

    // Fetch department name
    if ($department_id) {
        $stmt = $mysqli->prepare("SELECT name FROM departments WHERE id = ?");
        if (!$stmt) {
            die("Error preparing statement: " . $mysqli->error);
        }
        $stmt->bind_param('i', $department_id);
        $stmt->execute();
        $stmt->bind_result($department_name);

        if ($stmt->fetch()) {
            $response['department_name'] = $department_name;
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
    if (!$stmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $stmt->bind_result(
        $event_id,
        $event_name,
        $attendees,
        $item_id,
        $item_name,
        $quantity,
        $cost_per_item,
        $total_cost
    );

    while ($stmt->fetch()) {
        if (!isset($response['events'][$event_id])) {
            $response['events'][$event_id] = [
                'event_id' => $event_id,
                'event_name' => $event_name,
                'attendees' => $attendees,
                'items' => [],
            ];
        }

        if ($item_id !== null) {
            $response['events'][$event_id]['items'][] = [
                'item_id' => $item_id,
                'item_name' => $item_name,
                'quantity' => $quantity,
                'cost_per_item' => $cost_per_item,
                'total_cost' => $total_cost,
            ];
        }
    }
    $stmt->close();

    // Normalize events array
    $response['events'] = array_values($response['events']);

    // Fetch assets
    $stmt = $mysqli->prepare("
        SELECT 
            id AS asset_id, 
            item_name, 
            quantity, 
            cost_per_item, 
            total_cost
        FROM assets
        WHERE budget_id = ?
    ");
    if (!$stmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $stmt->bind_result(
        $asset_id,
        $item_name,
        $quantity,
        $cost_per_item,
        $total_cost
    );

    while ($stmt->fetch()) {
        $response['assets'][] = [
            'asset_id' => $asset_id,
            'item_name' => $item_name,
            'quantity' => $quantity,
            'cost_per_item' => $cost_per_item,
            'total_cost' => $total_cost,
        ];
    }
    $stmt->close();

    // Send response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(["error" => "An unexpected error occurred"]);
}
