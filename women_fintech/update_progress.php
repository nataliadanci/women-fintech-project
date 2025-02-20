<?php
ob_start(); // Activează output buffering

session_start(); // Inițiază sesiunea

require_once 'config/database.php';
include_once "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $sessionId = $_GET['id'];

    // Obținem detaliile programării
    $query = "
        SELECT ss.session_date, ss.session_time,
               m.first_name AS partner_first_name, m.last_name AS partner_last_name,
               sp.task, sp.status, sp.feedback
        FROM scheduled_sessions ss
        LEFT JOIN session_progress sp ON ss.id = sp.session_id
        JOIN members m ON 
            (m.id = ss.mentor_id AND ss.mentee_id = :user_id) OR 
            (m.id = ss.mentee_id AND ss.mentor_id = :user_id)
        WHERE ss.id = :session_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
    $stmt->execute();
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        echo "Programarea nu a fost găsită!";
        exit();
    }

    // Procesăm actualizările
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $task = $_POST['task'];
        $status = $_POST['status'];
        $feedback = $_POST['feedback'];

        $queryUpdate = "
            INSERT INTO session_progress (session_id, task, status, feedback)
            VALUES (:session_id, :task, :status, :feedback)
            ON DUPLICATE KEY UPDATE
                task = :task, status = :status, feedback = :feedback";
        $stmtUpdate = $db->prepare($queryUpdate);
        $stmtUpdate->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':task', $task, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':status', $status, PDO::PARAM_STR);
        $stmtUpdate->bindParam(':feedback', $feedback, PDO::PARAM_STR);

        if ($stmtUpdate->execute()) {
            header("Location: track_progress.php");
            exit(); // Oprește execuția pentru a preveni alte ieșiri
        } else {
            $message = "Eroare la actualizarea progresului!";
        }
    }
} else {
    echo "ID-ul programării nu a fost furnizat!";
    exit();
}
?>


<div class="form-container">
    <h2>Actualizează programarea</h2>
    <?php if (isset($message)): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="task">Task:</label>
            <textarea id="task" name="task" class="form-control"><?php echo htmlspecialchars($session['task'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="status">Stare:</label>
            <select id="status" name="status" class="form-control">
                <option value="pending" <?php echo ($session['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo ($session['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
            </select>
        </div>
        <div class="form-group">
            <label for="feedback">Feedback:</label>
            <textarea id="feedback" name="feedback" class="form-control"><?php echo htmlspecialchars($session['feedback'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Actualizează</button>
    </form>
</div>

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

