<?php
// =====================================================
// Database connection settings
// Edit these if your MySQL username/password is different
// (XAMPP default is username 'root' with an empty password)
// =====================================================

$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "farm_system";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session on every page that includes this file
session_start();
?>
