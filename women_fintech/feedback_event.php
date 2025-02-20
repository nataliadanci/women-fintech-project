<?php
session_start();
include_once "config/database.php";
include_once "includes/header.php";

// Verificăm dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    echo "Trebuie să fii autentificat";
    header('Location: login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = $_SESSION['user_id'];
    $event_id = $_POST['event_id'];
    $feedback = $_POST['feedback'];
    $rating = $_POST['rating'];

    // Adăugăm feedback-ul în baza de date
    $query = "INSERT INTO event_feedback (event_id, member_id, feedback, rating) VALUES (:event_id, :member_id, :feedback, :rating)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':feedback', $feedback, PDO::PARAM_STR);
    $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
    $stmt->execute();

    echo "Feedback trimis!";
}
?>
<?php include_once "includes/footer.php"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Verifică dacă utilizatorul are o preferință de temă salvată
        if (localStorage.getItem('theme') === 'dark') {
            body.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️';  // Icona pentru tema deschisă
        }

        // Schimbă tema la apăsarea butonului
        themeToggle.addEventListener('click', () => {
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '🌙';  // Icona pentru tema închisă
            } else {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '☀️';  // Icona pentru tema deschisă
            }
        });
    });
</script>
