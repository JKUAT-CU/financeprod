<?php
require_once 'db.php'; // Include the database connection file

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Retrieve the JSON body from the frontend
$inputData = json_decode(file_get_contents('php://input'), true);

// Validate the input
if (!isset($inputData['budgetId']) || !is_numeric($inputData['budgetId']) || !isset($inputData['events'])) {
    echo json_encode(['error' => 'Invalid input data']);
    exit;
}

$budgetId = $inputData['budgetId'];
$events = $inputData['events'];

// Prepare SQL for inserting finance suggestions
$query = "
    INSERT INTO finance_suggestions (item_type, item_id, suggested_amount, justification, created_at, updated_at)
    VALUES (?, ?, ?, ?, NOW(), NOW())
    ON DUPLICATE KEY UPDATE 
    suggested_amount = VALUES(suggested_amount), 
    justification = VALUES(justification),
    updated_at = NOW()
";

// Initialize a prepared statement
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit;
}

// Process each event and its items
foreach ($events as $event) {
    foreach ($event['items'] as $item) {
        $itemType = $item['item_type']; // Example: 'asset', 'event_item'
        $itemId = $item['item_id'];
        $suggestedAmount = $item['finance_cost'];
        $justification = isset($item['comment']) ? $item['comment'] : null;

        // Bind parameters and execute the statement
        $stmt->bind_param('sids', $itemType, $itemId, $suggestedAmount, $justification);

        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Failed to save data for item: ' . $itemId]);
            exit;
        }
    }
}

// Close the statement and connection
$stmt->close();
$mysqli->close();

echo json_encode(['message' => 'Finance suggestions saved successfully']);
