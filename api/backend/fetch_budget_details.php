<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

$budgetId = filter_input(INPUT_GET, 'budgetId', FILTER_VALIDATE_INT);
if (!$budgetId) {
    echo json_encode(["error" => "Invalid or missing budgetId parameter"]);
    exit;
}

$response = [
    'budgetDetails' => null,
    'events' => [],
    'assets' => [],
    'department_name' => null,
];

try {
    $stmt = $mysqli->prepare("
        SELECT b.id, b.department_id, b.status, d.name AS department_name
        FROM budgets b
        LEFT JOIN departments d ON b.department_id = d.id
        WHERE b.id = ?
    ");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $mysqli->error);
    }
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $stmt->bind_result($id, $department_id, $status, $department_name);
    if ($stmt->fetch()) {
        $response['budgetDetails'] = [
            'id' => $id,
            'department_id' => $department_id,
            'status' => $status,
        ];
        $response['department_name'] = $department_name;
    }
    $stmt->close();

    if (!$response['budgetDetails']) {
        echo json_encode(["error" => "Budget not found"]);
        exit;
    }

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
    ");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement for events: " . $mysqli->error);
    }
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

    $stmt = $mysqli->prepare("
        SELECT id, item_name, quantity, cost_per_item, total_cost
        FROM assets
        WHERE budget_id = ?
    ");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement for assets: " . $mysqli->error);
    }
    $stmt->bind_param('i', $budgetId);
    $stmt->execute();
    $stmt->bind_result($asset_id, $item_name, $quantity, $cost_per_item, $total_cost);
    while ($stmt->fetch()) {
        $response['assets'][] = [
            'id' => $asset_id,
            'item_name' => $item_name,
            'quantity' => $quantity,
            'cost_per_item' => $cost_per_item,
            'total_cost' => $total_cost,
        ];
    }
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
?>
