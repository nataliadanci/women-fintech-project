<?php
session_start();
include_once "config/database.php";

if (!isset($_SESSION['user_id'])) {  // Schimbăm pentru a verifica user_id
    echo "Trebuie să fii autentificat pentru a te înscrie la eveniment.";
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$member_id = $_SESSION['user_id'];  // Folosim user_id în loc de member_id
$event_id = $_POST['event_id'];

// Verificăm dacă utilizatorul este deja înscris
$check_query = "SELECT * FROM event_registrations WHERE member_id = :member_id AND event_id = :event_id";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
$check_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
$check_stmt->execute();

if ($check_stmt->rowCount() > 0) {
    echo "Ești deja înscris la acest eveniment.";
    header('Location: event_details.php?id=' . $event_id);
    exit();
}

// Inserăm înscrierea în baza de date
$query = "INSERT INTO event_registrations (member_id, event_id, status) VALUES (:member_id, :event_id, 'confirmed')";
$stmt = $db->prepare($query);
$stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
$stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    header('Location: event_details.php?id=' . $event_id);
    exit();
} else {
    echo "A apărut o eroare la înscriere.";
    exit();
}
?>
