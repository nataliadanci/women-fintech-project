<?php
ob_start(); // Start output buffering
require_once 'config/database.php';
include_once "includes/header.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$database = new Database();
$db = $database->getConnection();

// Paginare
$appointmentsPerPage = 6; // Programări pe pagină
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Pagina curentă
$offset = ($currentPage - 1) * $appointmentsPerPage;

// Numărul total de programări
$countQuery = "
    SELECT COUNT(DISTINCT ss.id) AS total
    FROM scheduled_sessions ss
    WHERE ss.mentor_id = :user_id OR ss.mentee_id = :user_id
";
$countStmt = $db->prepare($countQuery);
$countStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$countStmt->execute();
$totalAppointments = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calculăm numărul total de pagini
$totalPages = ceil($totalAppointments / $appointmentsPerPage);

// Obținem programările utilizatorului cu ultima actualizare
$query = "
    SELECT ss.id AS session_id, ss.session_date, ss.session_time,
           m.first_name AS partner_first_name, m.last_name AS partner_last_name,
           sp.task, sp.status, sp.feedback
    FROM scheduled_sessions ss
    LEFT JOIN (
        SELECT sp1.*
        FROM session_progress sp1
        INNER JOIN (
            SELECT session_id, MAX(updated_at) AS latest_update
            FROM session_progress
            GROUP BY session_id
        ) sp2
        ON sp1.session_id = sp2.session_id AND sp1.updated_at = sp2.latest_update
    ) sp ON ss.id = sp.session_id
    JOIN members m ON 
        (m.id = ss.mentor_id AND ss.mentee_id = :user_id) OR 
        (m.id = ss.mentee_id AND ss.mentor_id = :user_id)
    WHERE ss.mentor_id = :user_id OR ss.mentee_id = :user_id
    ORDER BY ss.session_date ASC, ss.session_time ASC
    LIMIT :offset, :limit
";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $appointmentsPerPage, PDO::PARAM_INT);
$stmt->execute();
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="page-title">Programări</h2>

<div class="events-list">
    <?php if (empty($sessions)): ?>
        <p>Nu există programări disponibile pentru afișare.</p>
    <?php else: ?>
        <?php foreach ($sessions as $session): ?>
            <div class="event-item">
                <h3>Programare: <?php echo date("d-m-Y", strtotime($session['session_date'])); ?></h3>
                <p><strong>Ora:</strong> <?php echo htmlspecialchars($session['session_time']); ?></p>
                <p><strong>Partener:</strong> <?php echo htmlspecialchars($session['partner_first_name'] . ' ' . $session['partner_last_name']); ?></p>
                <p><strong>Task:</strong> <?php echo htmlspecialchars($session['task'] ?? 'N/A'); ?></p>
                <p><strong>Stare:</strong> <?php echo htmlspecialchars($session['status'] ?? 'pending'); ?></p>
                <p><strong>Feedback:</strong> <?php echo htmlspecialchars($session['feedback'] ?? 'N/A'); ?></p>
                <p class="action-links">
                    <a href="update_progress.php?id=<?php echo $session['session_id']; ?>">Editează</a> |
                    <a href="delete_progress.php?id=<?php echo $session['session_id']; ?>" onclick="return confirm('Ești sigur că vrei să ștergi această programare?');">Șterge</a>
                </p>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Navigare pentru paginare -->
<div class="pagination">
    <?php if ($currentPage > 1): ?>
        <a href="?page=<?php echo $currentPage - 1; ?>" class="prev">Înapoi</a>
    <?php endif; ?>

    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
        <a href="?page=<?php echo $page; ?>" class="<?php echo ($page == $currentPage) ? 'active' : ''; ?>">
            <?php echo $page; ?>
        </a>
    <?php endfor; ?>

    <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?php echo $currentPage + 1; ?>" class="next">Înainte</a>
    <?php endif; ?>
</div>

<?php include_once "includes/footer.php"; ?>
<?php ob_end_flush(); ?>

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
