<?php
include_once "config/database.php";
include_once "includes/header.php";

// Get the database connection
$database = new Database();
$pdo = $database->getConnection();

// Search, filter, and pagination parameters
$search = $_GET['search'] ?? '';
$jobLevelFilter = $_GET['job_level'] ?? 'all';
$locationFilter = $_GET['location'] ?? 'all';
$perPage = 6; // Number of jobs per page
$page = (int)($_GET['page'] ?? 1);
$page = $page > 0 ? $page : 1;
$offset = ($page - 1) * $perPage;

// Build the SQL query for jobs
$query = "SELECT * FROM jobs WHERE 1=1";
if ($jobLevelFilter !== 'all') {
    $query .= " AND job_level = :job_level";
}
if ($locationFilter !== 'all') {
    $query .= " AND location = :location";
}
if (!empty($search)) {
    $query .= " AND (title LIKE :search OR company LIKE :search OR location LIKE :search)";
}
$query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
if ($jobLevelFilter !== 'all') {
    $stmt->bindValue(':job_level', $jobLevelFilter, PDO::PARAM_STR);
}
if ($locationFilter !== 'all') {
    $stmt->bindValue(':location', $locationFilter, PDO::PARAM_STR);
}
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of jobs for pagination
$countQuery = "SELECT COUNT(*) FROM jobs WHERE 1=1";
if ($jobLevelFilter !== 'all') {
    $countQuery .= " AND job_level = :job_level";
}
if ($locationFilter !== 'all') {
    $countQuery .= " AND location = :location";
}
if (!empty($search)) {
    $countQuery .= " AND (title LIKE :search OR company LIKE :search OR location LIKE :search)";
}
$countStmt = $pdo->prepare($countQuery);
if ($jobLevelFilter !== 'all') {
    $countStmt->bindValue(':job_level', $jobLevelFilter, PDO::PARAM_STR);
}
if ($locationFilter !== 'all') {
    $countStmt->bindValue(':location', $locationFilter, PDO::PARAM_STR);
}
if (!empty($search)) {
    $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$countStmt->execute();
$totalJobs = $countStmt->fetchColumn();
$totalPages = ceil($totalJobs / $perPage);
?>

<h2>Joburi</h2>

<div class="page-container">
    <form method="GET" class="filter-search-container filter_online">
        <div class="form-group">
            <label for="job_level">Filtrează în funcție de nivel:</label><br>
            <select name="job_level" id="filter_online" onchange="this.form.submit()">
                <option value="all" <?= $jobLevelFilter === 'all' ? 'selected' : '' ?>>Toate</option>
                <option value="internship" <?= $jobLevelFilter === 'internship' ? 'selected' : '' ?>>Internship</option>
                <option value="entry-level" <?= $jobLevelFilter === 'entry-level' ? 'selected' : '' ?>>Entry-level</option>
                <option value="mid-level" <?= $jobLevelFilter === 'mid-level' ? 'selected' : '' ?>>Mid-level</option>
                <option value="senior" <?= $jobLevelFilter === 'senior' ? 'selected' : '' ?>>Senior</option>
                <option value="manager" <?= $jobLevelFilter === 'manager' ? 'selected' : '' ?>>Manager</option>
            </select> <br><br>
            <label for="location">Filtrează în funcție de locație:</label><br>
            <select name="location" id="filter_online" onchange="this.form.submit()">
                <option value="all" <?= $locationFilter === 'all' ? 'selected' : '' ?>>Toate</option>
                <option value="bucharest" <?= $locationFilter === 'bucharest' ? 'selected' : '' ?>>București</option>
                <option value="cluj-napoca" <?= $locationFilter === 'cluj-napoca' ? 'selected' : '' ?>>Cluj-Napoca</option>
                <option value="timisoara" <?= $locationFilter === 'timisoara' ? 'selected' : '' ?>>Timișoara</option>
                <option value="iasi" <?= $locationFilter === 'iasi' ? 'selected' : '' ?>>Iași</option>
                <option value="brasov" <?= $locationFilter === 'brasov' ? 'selected' : '' ?>>Brașov</option>
            </select>
        </div>
        <div class="form-group search">
            <input type="text" name="search" placeholder="Caută prin joburi..." value="<?= htmlspecialchars($search); ?>">
        </div>
    </form>
</div>

<div class="jobs-list">
    <?php foreach ($jobs as $job): ?>
        <div class="jobs-item">
            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
            <p><strong>Companie:</strong> <?= htmlspecialchars($job['company']); ?></p>
            <p><strong>Nivel job:</strong> <?= htmlspecialchars($job['job_level']); ?></p>
            <p><strong>Locație:</strong> <?= htmlspecialchars($job['location']); ?></p>
            <a href="apply_to_job.php?id=<?php echo $job['id']; ?>">Aplică</a> |
            <a href="job_details.php?id=<?php echo $job['id']; ?>">Detalii</a> |
            <a href="edit_job.php?id=<?php echo $job['id']; ?>">Editează</a> |
            <a href="delete_job.php?id=<?php echo $job['id']; ?>" onclick="return confirm('Ești sigur că vrei să ștergi aceast job?');">Șterge</a>
        </div>
    <?php endforeach; ?>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1; ?>&job_level=<?= $jobLevelFilter; ?>&location=<?= $locationFilter; ?>&search=<?= htmlspecialchars($search); ?>" class="prev">Înapoi</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i; ?>&job_level=<?= $jobLevelFilter; ?>&location=<?= $locationFilter; ?>&search=<?= htmlspecialchars($search); ?>" class="<?= $i == $page ? 'active' : ''; ?>"><?= $i; ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1; ?>&job_level=<?= $jobLevelFilter; ?>&location=<?= $locationFilter; ?>&search=<?= htmlspecialchars($search); ?>" class="next">Înainte</a>
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



