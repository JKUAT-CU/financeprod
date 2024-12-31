<?php
// Start session and include database connection
session_start();
require 'db.php'; // Include database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input data
    if (empty($email) || empty($password)) {
        echo "<script>alert('Please fill in both fields');</script>";
    } else {
        // Query to get the user by email
        $query = "SELECT u.id, u.email, u.password, u.role_id, u.department_id, d.name AS department_name, r.name AS role_name
                  FROM users u
                  JOIN departments d ON u.department_id = d.id
                  JOIN roles r ON u.role_id = r.id
                  WHERE u.email = ?";

        // Use $mysqli for the connection
        if ($stmt = $mysqli->prepare($query)) {
            // Bind parameters
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            // Check if the email exists in the database
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userId, $userEmail, $hashedPassword, $roleId, $departmentId, $departmentName, $roleName);
                $stmt->fetch();
                
                // Verify password
                if (password_verify($password, $hashedPassword)) {
                    // Store session variables
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $userEmail;
                    $_SESSION['role'] = $roleName;
                    $_SESSION['role_id'] = $roleId;
                    $_SESSION['department'] = $departmentName;
                    $_SESSION['department_id'] = $departmentId;
                    
                    // Redirect user based on role
                    if ($roleName === 'Superadmin') {
                        header("Location: ../index.php"); // Superadmin dashboard
                    } elseif ($roleName === 'Admin') {
                        header("Location: ../index.php"); // Admin dashboard
                    } else {
                        header("Location: ../index.php"); // Viewer dashboard
                    }
                    exit(); // Ensure no further code is executed after redirect
                } else {
                    echo "<script>alert('Invalid password');</script>";
                }
            } else {
                echo "<script>alert('No user found with that email');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Database error');</script>";
        }
    }
}
?>
