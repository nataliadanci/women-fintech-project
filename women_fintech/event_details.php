<?php
ob_start(); // Activează output buffering

session_start(); // Inițiază sesiunea

require_once 'config/database.php';
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

// Verificăm dacă există un ID de eveniment în URL
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Obținem detalii despre eveniment
    $query = "SELECT * FROM events WHERE id = :event_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dacă evenimentul nu există
    if (!$event) {
        echo "Evenimentul nu a fost găsit!";
        exit();
    }

    // Obținem feedback-urile asociate cu acest eveniment
    $feedback_query = "SELECT f.feedback, f.rating, m.username FROM event_feedback f
                       JOIN members m ON f.member_id = m.id
                       WHERE f.event_id = :event_id";
    $feedback_stmt = $db->prepare($feedback_query);
    $feedback_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $feedback_stmt->execute();
    $feedbacks = $feedback_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "ID-ul evenimentului nu a fost furnizat!";
    exit();
}
?>

<h2>Detalii Eveniment</h2>
<h3><?php echo htmlspecialchars($event['title']); ?></h3>
<p><strong>Data:</strong> <?php echo date("d-m-Y H:i", strtotime($event['event_date'])); ?></p>
<p><strong>Locație:</strong> <?php echo $event['is_online'] ? "Online" : htmlspecialchars($event['location']); ?></p>

<h3>Feedback-uri</h3>
<?php if ($feedbacks): ?>
    <ul>
        <?php foreach ($feedbacks as $feedback): ?>
            <li>
                <p><strong><?php echo htmlspecialchars($feedback['username']); ?>:</strong></p>
                <p><strong>Rating:</strong> <?php echo $feedback['rating']; ?>/5</p>
                <p><?php echo nl2br(htmlspecialchars($feedback['feedback'])); ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Nu există feedback pentru acest eveniment încă.</p>
<?php endif; ?>

<!-- Formularul de feedback -->
<form class="feedback-form" action="feedback_event.php?id=<?php echo $event_id; ?>" method="POST">
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
    <textarea name="feedback" placeholder="Lasă feedback"></textarea>
    <input type="number" name="rating" min="1" max="5" placeholder="Rating">
    <button id="feedback-btn" type="submit">Trimite</button>
</form>

<?php
$is_registered = false;

if (isset($_SESSION['user_id'])) {
    $check_query = "SELECT * FROM event_registrations 
                    WHERE member_id = :member_id AND event_id = :event_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':member_id', $_SESSION['user_id'], PDO::PARAM_INT);  // Folosim user_id
    $check_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $is_registered = $check_stmt->rowCount() > 0;
}
?>

<!-- Formular pentru înscriere la eveniment -->
<?php if (isset($_SESSION['member_id']) || isset($_SESSION['user_id'])): ?>
    <?php if ($is_registered): ?>
        <p>Înscriere reușită!</p>
    <?php else: ?>
        <form class="register-form" action="register_event.php" method="POST">
            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
            <button id="register-btn" type="submit">Înscrie-te la eveniment</button>
        </form>
    <?php endif; ?>
<?php else: ?>
    <p><a href="login.php">Autentifică-te</a> pentru a te înscrie la acest eveniment.</p>
<?php endif; ?>

<?php include_once "includes/footer.php"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Check if user has a saved theme preference
        if (localStorage.getItem('theme') === 'dark') {
            body.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️';
        }

        // Toggle theme on button click
        themeToggle.addEventListener('click', () => {
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '🌙';
            } else {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '☀️';
            }
        });
    });
</script>
