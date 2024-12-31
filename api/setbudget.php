<?php
// Start session
session_start();
require 'backend/db.php'; // Include database connection

// Handle form submission to create or update timelines
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_timeline'])) {
        // Create a new activity timeline
        $name = $_POST['name'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        $query = "INSERT INTO activity_timelines (name, start_date, end_date) VALUES (?, ?, ?)";
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("sss", $name, $startDate, $endDate);
            $stmt->execute();
            echo "<script>alert('Activity timeline created successfully');</script>";
        } else {
            echo "<script>alert('Error creating activity timeline');</script>";
        }
    }

    if (isset($_POST['update_timeline'])) {
        // Update an existing timeline (extend the period)
        $timelineId = $_POST['timeline_id'];
        $newEndDate = $_POST['new_end_date'];

        $query = "UPDATE activity_timelines SET end_date = ? WHERE id = ?";
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("si", $newEndDate, $timelineId);
            $stmt->execute();
            echo "<script>alert('Activity timeline extended successfully');</script>";
        } else {
            echo "<script>alert('Error extending activity timeline');</script>";
        }
    }

    if (isset($_POST['open_budget_period'])) {
        // Open a specific budget period
        $timelineId = $_POST['timeline_id'];

        // Check if the budget period is already open, you can add more checks here
        $query = "SELECT * FROM budgets WHERE timeline_id = ? AND status = 'Pending'";
        if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("i", $timelineId);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                echo "<script>alert('No budgets found for this timeline');</script>";
            } else {
                $updateQuery = "UPDATE budgets SET status = 'Pending' WHERE timeline_id = ?";
                if ($updateStmt = $mysqli->prepare($updateQuery)) {
                    $updateStmt->bind_param("i", $timelineId);
                    $updateStmt->execute();
                    echo "<script>alert('Budget period opened successfully');</script>";
                }
            }
        }
    }
}

// Fetch all activity timelines and budgets
$query = "SELECT * FROM activity_timelines";
$result = $mysqli->query($query);
$timelines = $result->fetch_all(MYSQLI_ASSOC);

// Fetch the budgets linked to the timelines
$budgetQuery = "SELECT b.id, b.total_amount, b.status, b.created_at, at.name AS timeline_name
                FROM budgets b
                JOIN activity_timelines at ON b.timeline_id = at.id";
$budgetResult = $mysqli->query($budgetQuery);
$budgets = $budgetResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Timelines and Budgets</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJ03R3pE6d4A36xwE9YlzH6Ttw5IBh6yjp4zxj2iRZXaZsDLPjYgptFcv6wB" crossorigin="anonymous">
    <style>
        /* Custom Styling */
        .timeline-card, .budget-card {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .timeline-card h3, .budget-card h3 {
            font-size: 1.5rem;
            color: #007bff;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .form-group label {
            font-weight: 600;
        }

        .container {
            max-width: 1200px;
        }

        h2, h3 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <h2>Activity Timelines and Budgets</h2>

        <!-- Create New Timeline Form -->
        <div class="timeline-card">
            <h3>Create New Activity Timeline</h3>
            <form method="POST">
                <div class="form-group mb-4">
                    <label for="name">Timeline Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="e.g., '2024 Budgeting'" required>
                </div>
                <div class="form-group mb-4">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
                <div class="form-group mb-4">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
                <button type="submit" name="create_timeline" class="btn btn-custom">Create Timeline</button>
            </form>
        </div>

        <!-- List Existing Timelines -->
        <div class="mt-5">
            <h3>Existing Activity Timelines</h3>
            <div class="row">
                <?php foreach ($timelines as $timeline): ?>
                    <div class="col-md-4">
                        <div class="timeline-card">
                            <h3><?php echo htmlspecialchars($timeline['name']); ?></h3>
                            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($timeline['start_date']); ?></p>
                            <p><strong>End Date:</strong> <?php echo htmlspecialchars($timeline['end_date']); ?></p>
                            <form method="POST">
                                <input type="hidden" name="timeline_id" value="<?php echo $timeline['id']; ?>">
                                <div class="form-group mb-3">
                                    <label for="new_end_date">Extend End Date:</label>
                                    <input type="date" name="new_end_date" id="new_end_date" class="form-control" required>
                                </div>
                                <button type="submit" name="update_timeline" class="btn btn-custom">Extend Timeline</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- List Existing Budgets -->
        <div class="mt-5">
            <h3>Previous Budgets</h3>
            <div class="row">
                <?php foreach ($budgets as $budget): ?>
                    <div class="col-md-4">
                        <div class="budget-card">
                            <h3>Budget for <?php echo htmlspecialchars($budget['timeline_name']); ?></h3>
                            <p><strong>Total Amount:</strong> <?php echo number_format($budget['total_amount'], 2); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($budget['status']); ?></p>
                            <p><strong>Created At:</strong> <?php echo htmlspecialchars($budget['created_at']); ?></p>
                            <form method="POST">
                                <input type="hidden" name="timeline_id" value="<?php echo $budget['timeline_id']; ?>">
                                <button type="submit" name="open_budget_period" class="btn btn-custom">Open Budget Period</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gyb6V0Ffj7xpg6a3yn4Ck5a0D1qjxy3p38am6f5bBqjm/j2bT4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0pQJAf0mP2T4w0v0VoZjozZ5XYcsdi3l5PQO2v8+J76cR7z1" crossorigin="anonymous"></script>
</body>
</html>
