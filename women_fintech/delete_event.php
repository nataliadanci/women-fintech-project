<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Pregătim interogarea pentru a șterge evenimentul din baza de date
    $query = "DELETE FROM events WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);

    // Executăm interogarea
    if ($stmt->execute()) {
        // După ce ștergem evenimentul, redirecționăm utilizatorul către pagina cu lista de evenimente
        header("Location: events.php");
        exit();
    } else {
        echo "A apărut o eroare la ștergerea evenimentului.";
    }
} else {
    echo "ID-ul evenimentului nu a fost specificat.";
}
?>
