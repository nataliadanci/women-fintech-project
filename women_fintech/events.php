<?php
require_once 'config/database.php';
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

// Preluăm valoarea filtrului din URL, dacă este setată
$filter_online = isset($_GET['filter_online']) ? $_GET['filter_online'] : '';

// Paginare
$eventsPerPage = 6;  // Evenimente pe pagină
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Pagina curentă
$offset = ($currentPage - 1) * $eventsPerPage;

// Construim interogarea SQL de bază
$query = "SELECT * FROM events";

// Dacă există un filtru, adăugăm clauza WHERE pentru a filtra evenimentele online/offline
if ($filter_online !== '') {
    $query .= " WHERE is_online = :filter_online";
}

// Adăugăm ordonarea evenimentelor după data evenimentului
$query .= " ORDER BY event_date ASC LIMIT :offset, :eventsPerPage";

// Pregătim și executăm interogarea SQL
$stmt = $db->prepare($query);

// Dacă există un filtru, legăm parametrul pentru a-l folosi în interogare
if ($filter_online !== '') {
    $stmt->bindParam(':filter_online', $filter_online, PDO::PARAM_INT);
}

// Leagă parametrii LIMIT și OFFSET
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':eventsPerPage', $eventsPerPage, PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculăm numărul total de evenimente
$queryCount = "SELECT COUNT(*) as total FROM events";
if ($filter_online !== '') {
    $queryCount .= " WHERE is_online = :filter_online";
}
$stmtCount = $db->prepare($queryCount);
if ($filter_online !== '') {
    $stmtCount->bindParam(':filter_online', $filter_online, PDO::PARAM_INT);
}
$stmtCount->execute();
$totalEvents = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

// Calculăm numărul total de pagini
$totalPages = ceil($totalEvents / $eventsPerPage);
?>

<h2>Lista Evenimentelor</h2>

<!-- Formularul pentru filtrare -->
<form method="GET" action="events.php">
    <select name="filter_online" id="filter_online" onchange="this.form.submit()">
        <option value="">Toate evenimentele</option>
        <option value="1" <?php echo (isset($_GET['filter_online']) && $_GET['filter_online'] == '1') ? 'selected' : ''; ?>>Evenimente online</option>
        <option value="0" <?php echo (isset($_GET['filter_online']) && $_GET['filter_online'] == '0') ? 'selected' : ''; ?>>Evenimente offline</option>
    </select>
</form>

<br>

<!-- Link pentru adăugarea unui eveniment nou -->
<p><a href="add_event.php">Adaugă un nou eveniment</a></p>

<div class="events-list">
    <?php foreach ($events as $event): ?>
        <div class="event-item">
            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
            <p><strong>Data:</strong> <?php echo date("d-m-Y H:i", strtotime($event['event_date'])); ?></p>
            <p><strong>Locație:</strong> <?php echo $event['is_online'] ? "Online" : htmlspecialchars($event['location']); ?></p>
            <a href="event_details.php?id=<?php echo $event['id']; ?>">Detalii</a> |
            <a href="edit_event.php?id=<?php echo $event['id']; ?>">Editează</a> |
            <a href="delete_event.php?id=<?php echo $event['id']; ?>" onclick="return confirm('Ești sigur că vrei să ștergi acest eveniment?');">Șterge</a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Navigare pentru paginare -->
<div class="pagination">
    <?php if ($currentPage > 1): ?>
        <a href="?page=<?php echo $currentPage - 1; ?>&filter_online=<?php echo urlencode($filter_online); ?>" class="prev">Înapoi</a>
    <?php endif; ?>

    <?php for ($page = 1; $page <= $totalPages; $page++): ?>
        <a href="?page=<?php echo $page; ?>&filter_online=<?php echo urlencode($filter_online); ?>" class="<?php echo ($page == $currentPage) ? 'active' : ''; ?>">
            <?php echo $page; ?>
        </a>
    <?php endfor; ?>

    <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?php echo $currentPage + 1; ?>&filter_online=<?php echo urlencode($filter_online); ?>" class="next">Înainte</a>
    <?php endif; ?>
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
