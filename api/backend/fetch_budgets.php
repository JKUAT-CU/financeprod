<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

try {
    $sql = "
        SELECT 
            d.id AS department_id, 
            d.name AS department_name, 
            b.id AS budget_id, 
            b.total_amount, 
            b.status
        FROM departments d
        JOIN budgets b ON d.id = b.department_id
    ";

    $result = $mysqli->query($sql);

    if (!$result) {
        throw new Exception("Failed to fetch budgets: " . $mysqli->error);
    }

    $budgets = [];
    while ($row = $result->fetch_assoc()) {
        $budgets[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($budgets);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
?>
