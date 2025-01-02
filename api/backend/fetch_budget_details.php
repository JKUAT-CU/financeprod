<?php
// Load environment variables (use dotenv if needed)
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'jkuatcu_devs';
$password = getenv('DB_PASS') ?: '#God@isAble!#';
$database = getenv('DB_NAME') ?: 'jkuatcu_admin';

// Database connection
$mysqli = new mysqli($host, $user, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Validate input
$budgetId = filter_input(INPUT_GET, 'budgetId', FILTER_VALIDATE_INT);
if (!$budgetId) {
    echo json_encode(["error" => "Invalid or missing budgetId"]);
    exit;
}

$response = [
    'budgetDetails' => null,
    'events' => [],
    'assets' => [],
    'department_name' => null,
];

try {
    // Fetch budget details
    $stmt = $mysqli->prepare("SELECT id, department_id, name, status FROM budgets WHERE id = ?");
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($budget = $result->fetch_assoc()) {
        $response['budgetDetails'] = $budget;
    } else {
        echo json_encode(["error" => "Budget not found"]);
        exit;
    }

    $department_id = $budget['department_id'];

    // Fetch department name
    if ($department_id) {
        $stmt = $mysqli->prepare("SELECT name FROM departments WHERE id = ?");
        $stmt->bind_param('i', $department_id);
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
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $eventId = $row['event_id'];
        if (!isset($response['events'][$eventId])) {
            $response['events'][$eventId] = [
                'event_id' => $eventId,
                'event_name' => $row['event_name'],
                'attendees' => $row['attendees'],
                'items' => [],
            ];
        }
        if ($row['item_id']) {
            $response['events'][$eventId]['items'][] = [
                'item_id' => $row['item_id'],
                'item_name' => $row['item_name'],
                'quantity' => $row['quantity'],
                'cost_per_item' => $row['cost_per_item'],
                'total_cost' => $row['total_cost'],
            ];
        }
    }

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
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($asset = $result->fetch_assoc()) {
        $response['assets'][] = $asset;
    }

    // Output JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "An unexpected error occurred"]);
}
