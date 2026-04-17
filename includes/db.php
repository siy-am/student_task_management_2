<?php
// includes/db.php - Simple database connection
$servername = "localhost";
$username = "root";      // XAMPP default
$password = "";          // XAMPP default (empty for XAMPP)
$dbname = "student_task_db"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>