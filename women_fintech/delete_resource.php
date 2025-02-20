<?php
session_start();
require_once "config/database.php";

$database = new Database();
$db = $database->getConnection();

// Verificare rol utilizator (admin sau mentor)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'mentor'])) {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

if (isset($_GET['id'])) {
    $resource_id = $_GET['id'];

    // Stergem resursa din baza de date
    $query = "DELETE FROM resources WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $resource_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: resources.php");
        exit();
    } else {
        echo "A apărut o eroare la ștergerea resursei.";
    }
} else {
    echo "ID-ul resursei nu a fost specificat.";
}
?>