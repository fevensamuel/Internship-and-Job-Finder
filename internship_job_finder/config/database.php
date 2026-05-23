<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'job_portal';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Keep schema up to date for account deletion feature
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'delete_request'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN delete_request ENUM('none', 'pending') DEFAULT 'none'");
}
?>
