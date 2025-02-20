<?php
include_once "config/database.php";
include_once "includes/header.php";

// Obține conexiunea la baza de date
$database = new Database();
$pdo = $database->getConnection();

// Parametrii pentru căutare, filtrare și paginare
$search = $_GET['search'] ?? '';
$filter = $_GET['category'] ?? 'all';
$perPage = 6; // Numărul de resurse pe pagină
$page = (int)($_GET['page'] ?? 1);
$page = $page > 0 ? $page : 1;
$offset = ($page - 1) * $perPage;

// Construiește interogarea SQL pentru articole
$query = "SELECT * FROM resources WHERE 1=1";
if ($filter !== 'all') {
    $query .= " AND category = :category";
}
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR author LIKE :search OR description LIKE :search)";
}
$query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
if ($filter !== 'all') {
    $stmt->bindValue(':category', $filter, PDO::PARAM_STR);
}
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obține numărul total de resurse pentru paginare
$countQuery = "SELECT COUNT(*) FROM resources WHERE 1=1";
if ($filter !== 'all') {
    $countQuery .= " AND category = :category";
}
if (!empty($search)) {
    $countQuery .= " AND (title LIKE :search OR author LIKE :search OR description LIKE :search)";
}
$countStmt = $pdo->prepare($countQuery);
if ($filter !== 'all') {
    $countStmt->bindValue(':category', $filter, PDO::PARAM_STR);
}
if (!empty($search)) {
    $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$countStmt->execute();
$totalResources = $countStmt->fetchColumn();
$totalPages = ceil($totalResources / $perPage);
?>

<h2>Resurse</h2>

<div class="page-container">
    <form method="GET" class="form-inline filter-search-container">
        <div class="form-group">
            <select name="category" id="filter_online" onchange="this.form.submit()">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Toate</option>
                <option value="article" <?= $filter === 'article' ? 'selected' : '' ?>>Articole</option>
                <option value="video" <?= $filter === 'video' ? 'selected' : '' ?>>Videouri</option>
                <option value="podcast" <?= $filter === 'podcast' ? 'selected' : '' ?>>Podcast-uri</option>
                <option value="download" <?= $filter === 'download' ? 'selected' : '' ?>>Descărcabile</option>
            </select>
        </div>

        <div class="form-group search">
            <input type="text" name="search" placeholder="Caută resurse..." value="<?= htmlspecialchars($search); ?>">
        </div>
    </form>
</div>

<div class="resources-list">
    <?php foreach ($resources as $resource): ?>
        <div class="resources-item">
            <h3><?php echo htmlspecialchars($resource['title']); ?></h3>
            <p><strong>Autor:</strong> <?= htmlspecialchars($resource['author']); ?></p>
            <p><strong>Tip resursa:</strong> <?= htmlspecialchars($resource['category']); ?></p>
            <a href="resource_details.php?id=<?php echo $resource['id']; ?>">Detalii</a> |
            <a href="edit_resource.php?id=<?php echo $resource['id']; ?>">Editează</a> |
            <a href="delete_resource.php?id=<?php echo $resource['id']; ?>" onclick="return confirm('Ești sigur că vrei să ștergi această resursă?');">Șterge</a>
        </div>
    <?php endforeach; ?>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1; ?>&category=<?= $filter; ?>&search=<?= htmlspecialchars($search); ?>" class="prev">Înapoi</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>&category=<?= $filter; ?>&search=<?= htmlspecialchars($search); ?>" class="<?= $i == $page ? 'active' : ''; ?>"><?= $i; ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1; ?>&category=<?= $filter; ?>&search=<?= htmlspecialchars($search); ?>" class="next">Înainte</a>
    <?php endif; ?>
</div>

<?php include_once "includes/footer.php"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Verifică preferința temei salvate
        if (localStorage.getItem('theme') === 'dark') {
            body.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️';
        }

        // Trecerea între teme
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

