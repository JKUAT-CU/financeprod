<?php

// Include the database connection file
include "db.php";

// File path for storing account numbers JSON
$filePath = 'accounts.json';

// File path for saving changes as JSON
$changesFilePath = 'changes.json';

// Function to load account numbers from JSON file
function loadAccountNumbers($filePath) {
    if (file_exists($filePath)) {
        $json = file_get_contents($filePath);
        return json_decode($json, true);
    } else {
        return array(); // Return an empty array if file doesn't exist
    }
}

// Function to save changes to a JSON file
function saveChangesToFile($filePath, $changes) {
    if (file_exists($filePath)) {
        $existingChanges = json_decode(file_get_contents($filePath), true);
        $changes = array_merge($existingChanges, $changes);
    }
    $json = json_encode($changes, JSON_PRETTY_PRINT);
    file_put_contents($filePath, $json);
}

// Function to find the closest match
function findClosestMatch($input, $validAccountNumbers) {
    $closestMatch = "others";
    foreach ($validAccountNumbers as $validString) {
        if (stripos($input, $validString) !== false) {
            $closestMatch = $validString;
            break;
        }
    }
    return $closestMatch;
}

// Load account numbers from JSON file
$validAccountNumbers = loadAccountNumbers($filePath);

// Initialize an array to collect changes
$changes = array();

// Step 1: Create a temporary backup of the current accounts table
$sqlBackup = "CREATE TEMPORARY TABLE accounts_backup AS SELECT * FROM accounts";
if ($conn->query($sqlBackup) === FALSE) {
    die("Error creating backup: " . $conn->error);
}

// Step 2: Clear the accounts table
$sqlClear = "TRUNCATE TABLE accounts";
if ($conn->query($sqlClear) === FALSE) {
    die("Error clearing accounts table: " . $conn->error);
}

// Step 3: Retrieve data from the finance table
$sql = "SELECT finance.*, accounts_backup.BillRefNumber AS existingBillRefNumber FROM finance LEFT JOIN accounts_backup ON finance.id = accounts_backup.id";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("Error retrieving data: " . $conn->error);
}

if ($result->num_rows > 0) {
    // Iterate over each row in the finance table
    while ($row = $result->fetch_assoc()) {
        $financeAccountNumber = strtolower($row["BillRefNumber"]); // Normalize to lowercase

        // Categorize account numbers
        if (preg_match("/thanks/i", $financeAccountNumber)) {
            $closestMatch = "thanksgiving";
        } elseif (preg_match("/rftb/i", $financeAccountNumber)) {
            $closestMatch = "RFTB";
        } elseif (preg_match("/^sm\d|^24/i", $financeAccountNumber) || stripos($financeAccountNumber, 'samburu') !== false) {
            $closestMatch = "missions";
        } elseif (preg_match("/car wash/i", $financeAccountNumber) || stripos($financeAccountNumber, 'finje ya mission')|| stripos($financeAccountNumber, '30yasamburu') !== false) { 
            $closestMatch = "carwash";
        } else {
            $closestMatch = findClosestMatch($financeAccountNumber, $validAccountNumbers);
        }

        // Update or insert data into the accounts table with the matched account number
        $id = $row["id"];
        $transID = $row["TransID"];
        $transTime = $row["TransTime"];
        $transAmount = $row["TransAmount"];
        $businessShortCode = $row["BusinessShortCode"];

        $sqlInsert = $conn->prepare("INSERT INTO accounts (id, TransID, TransTime, TransAmount, BusinessShortCode, BillRefNumber) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE BillRefNumber = ?");
        $sqlInsert->bind_param("isssiss", $id, $transID, $transTime, $transAmount, $businessShortCode, $closestMatch, $closestMatch);
        $sqlInsert->execute();

        // Check for changes and collect the results
        if ($row["existingBillRefNumber"] !== $closestMatch) {
            $changes[] = array(
                "ID" => $id,
                "OldBillRefNumber" => $row["existingBillRefNumber"],
                "NewBillRefNumber" => $closestMatch
            );
        }
    }
} else {
    echo "0 results";
}

// Drop the temporary backup table
$sqlDropBackup = "DROP TEMPORARY TABLE accounts_backup";
if ($conn->query($sqlDropBackup) === FALSE) {
    die("Error dropping backup table: " . $conn->error);
}

// Save the changes to a JSON file
saveChangesToFile($changesFilePath, $changes);

// Close the database connection
$conn->close();

?>
