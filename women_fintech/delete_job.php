<?php
session_start(); // Start the session
require_once "config/database.php";

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];

    // Stergem jobul din baza de date
    $query = "DELETE FROM jobs WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $job_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: jobs.php");
        exit();
    } else {
        echo "A apărut o eroare la ștergerea jobului.";
    }
} else {
    echo "ID-ul jobului nu a fost specificat.";
}
?>
