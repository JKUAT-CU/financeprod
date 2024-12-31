<?php
session_start();

// Debug: Print the entire session data
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo password_hash('test21@2025', PASSWORD_BCRYPT);
?>

