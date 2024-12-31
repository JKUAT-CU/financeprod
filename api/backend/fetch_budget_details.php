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
    'department_name' => null,
];

try {
    // Fetch budget details
    $stmt = $mysqli->prepare("SELECT * FROM budgets WHERE `id` = ?");
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $stmt->bind_result($id, $department_id, $name, $status); // Adjust fields as necessary
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
    $departmentId = $response['budgetDetails']['department_id'];
    if ($departmentId) {
        $stmt = $mysqli->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->bind_param('i', $departmentId);
        $stmt->execute();
        $stmt->bind_result($department_name);
        if ($stmt->fetch()) {
            $response['department_name'] = $department_name;
        }
        $stmt->close();
    }

    // Fetch events and items
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
    $stmt->bind_result($event_id, $event_name, $attendees, $item_id, $item_name, $quantity, $cost_per_item, $total_cost);

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
    $stmt->bind_result($asset_id, $item_name, $quantity, $cost_per_item, $total_cost);

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

    // Pass data to frontend
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(["error" => "An unexpected error occurred"]);
}
