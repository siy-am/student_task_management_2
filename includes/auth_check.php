<?php
// includes/auth_check.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
?>
