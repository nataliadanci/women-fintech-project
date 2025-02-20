<?php
session_start(); // Start the session
include_once "config/database.php";

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

if (isset($_GET['id'])) {
    $database = new Database();
    $db = $database->getConnection();

    $query = "DELETE FROM members WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
}
header("Location: members.php");
exit();
?>