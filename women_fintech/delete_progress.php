
<?php
require_once 'config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $sessionId = $_GET['id'];

    // Ștergem programarea
    $query = "DELETE FROM scheduled_sessions WHERE id = :session_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: track_progress.php");
        exit();
    } else {
        echo "Eroare la ștergerea programării!";
    }
} else {
    echo "ID-ul programării nu a fost furnizat!";
}
?>
