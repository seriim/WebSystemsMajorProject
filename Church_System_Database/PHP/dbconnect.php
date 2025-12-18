<?php
// dbconnect.php
// Database connection file for Church Management System

$servername = "localhost";      // usually 'localhost'
$username   = "root";           // your MySQL username
$password   = "";               // your MySQL password (empty if none)
$dbname     = "church_system_database"; // the database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to avoid encoding issues
$conn->set_charset("utf8");
?>