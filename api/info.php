<?php
session_start();

// Debug: Print the entire session data
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo password_hash('test25', PASSWORD_BCRYPT);
?>

