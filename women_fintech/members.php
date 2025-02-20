<?php
ob_start(); // Previne ieșirea neintenționată
include_once "config/database.php";
include_once "includes/header.php";

// Activare erori pentru debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database = new Database();
$db = $database->getConnection();

// Verificăm dacă este selectată o profesie
$selectedProfession = isset($_GET['profession']) ? $_GET['profession'] : '';

// Preia lista de profesii distincte din baza de date pentru filtrare
$queryProfessions = "SELECT DISTINCT profession FROM members ORDER BY profession ASC";
$stmtProfessions = $db->prepare($queryProfessions);
if (!$stmtProfessions->execute()) {
    die("Eroare la executarea interogării: " . implode(", ", $stmtProfessions->errorInfo()));
}
$professions = $stmtProfessions->fetchAll(PDO::FETCH_ASSOC);

// Verificăm tipul de sortare din URL
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Setăm valoarea pentru sortarea SQL
switch ($sort) {
    case 'name_asc':
        $order_by = "ORDER BY first_name ASC, last_name ASC";
        break;
    case 'name_desc':
        $order_by = "ORDER BY first_name DESC, last_name DESC";
        break;
    case 'date_asc':
        $order_by = "ORDER BY created_at ASC";
        break;
    case 'date_desc':
        $order_by = "ORDER BY created_at DESC";
        break;
    default:
        $order_by = "ORDER BY first_name ASC, last_name ASC"; // Valoare implicită
}

// Paginare
$membersPerPage = 3;  // Membri pe pagină
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Pagina curentă
$offset = ($currentPage - 1) * $membersPerPage;

// Adaugă filtrul de profesie în interogarea SQL dacă o profesie este selectată
$query = "SELECT * FROM members";
if ($selectedProfession) {
    $query .= " WHERE profession = :profession";
}
$query .= " $order_by LIMIT :offset, :membersPerPage"; // Adaugă LIMIT și OFFSET

$stmt = $db->prepare($query);

// Leagă parametrul profession dacă este setat
if ($selectedProfession) {
    $stmt->bindParam(':profession', $selectedProfession);
}

// Leagă parametrii LIMIT și OFFSET
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);  // Parametrul OFFSET
$stmt->bindParam(':membersPerPage', $membersPerPage, PDO::PARAM_INT);  // Parametrul LIMIT
if (!$stmt->execute()) {
    die("Eroare la executarea interogării: " . implode(", ", $stmt->errorInfo()));
}

// Obține numărul total de membri pentru calculul numărului de pagini
$queryCount = "SELECT COUNT(*) as total FROM members";
if ($selectedProfession) {
    $queryCount .= " WHERE profession = :profession";
}
$stmtCount = $db->prepare($queryCount);
if ($selectedProfession) {
    $stmtCount->bindParam(':profession', $selectedProfession);
}
if (!$stmtCount->execute()) {
    die("Eroare la executarea interogării: " . implode(", ", $stmtCount->errorInfo()));
}
$totalMembers = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

// Calculăm numărul total de pagini
$totalPages = ceil($totalMembers / $membersPerPage);

if ($totalMembers == 0) {
    echo "<p>Nu există membri de afișat.</p>";
    include_once "includes/footer.php";
    ob_end_flush(); // Închide buffer-ul și trimite ieșirea
    exit();
}

$currentPage = max(1, min($currentPage, $totalPages));
?>
    <h2>Membri</h2>

    <!-- Formular de sortare -->
    <div class="form-group">
        <label for="sort">Sortare după:</label>
        <select id="sort" class="form-control" onchange="window.location.href=this.value;">
            <option value="members.php?sort=name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Nume (A-Z)</option>
            <option value="members.php?sort=name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Nume (Z-A)</option>
            <option value="members.php?sort=date_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date_asc') ? 'selected' : ''; ?>>Dată (Ascendentă)</option>
            <option value="members.php?sort=date_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date_desc') ? 'selected' : ''; ?>>Dată (Descendentă)</option>
        </select>
    </div>

    <!-- Formular de filtrare după profesie -->
    <div class="form-group">
        <label for="profession">Filtru profesie:</label>
        <select id="profession" class="form-control filter_online" onchange="window.location.href='members.php?profession=' + this.value + '&sort=<?php echo $sort; ?>'">
            <option value="" <?php echo empty($selectedProfession) ? 'selected' : ''; ?>>Toate profesiile</option>
            <?php foreach ($professions as $profession): ?>
                <?php
                // Remove extra whitespace and sanitize values
                $professionName = trim($profession['profession']);
                ?>
                <option value="<?php echo htmlspecialchars($professionName); ?>" <?php echo ($selectedProfession == $professionName) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($professionName); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Listarea membrilor -->
    <div class="row">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4">
                    <div class="card member-card">
                        <div class="card-body">
                            <div class="content-container">
                                <div class="text-container">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></a></h5>
                                    <p class="card-text">
                                        <strong>Profesie:</strong> <?php echo htmlspecialchars($row['profession']); ?><br>
                                        <strong>Companie:</strong> <?php echo htmlspecialchars($row['company']); ?>
                                    </p>
                                </div>
                                <img class="profile-picture" src="<?php echo !empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : 'default-profile.png'; ?>" alt="Profile Picture" width="100" height="100">
                            </div>
                            <div class="btnContainer">
                                <a href="member_details.php?id=<?php echo $row['id']; ?>">Detalii</a> |
                                <a href="edit_member.php?id=<?php echo $row['id']; ?>">Editează</a> |
                                <a href="delete_member.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Ești sigur că vrei să ștergi acest eveniment?');">Șterge</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Nu există membri care să corespundă criteriilor tale.</p>
        <?php endif; ?>
    </div>

    <!-- Navigare pentru paginare -->
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?php echo $currentPage - 1; ?>&profession=<?php echo urlencode($selectedProfession); ?>&sort=<?php echo urlencode($sort); ?>" class="prev">Înapoi</a>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
            <a href="?page=<?php echo $page; ?>&profession=<?php echo urlencode($selectedProfession); ?>&sort=<?php echo urlencode($sort); ?>" class="<?php echo ($page == $currentPage) ? 'active' : ''; ?>">
                <?php echo $page; ?>
            </a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?php echo $currentPage + 1; ?>&profession=<?php echo urlencode($selectedProfession); ?>&sort=<?php echo urlencode($sort); ?>" class="next">Înainte</a>
        <?php endif; ?>
    </div>
<?php
include_once "includes/footer.php";
ob_end_flush(); // Închide buffer-ul și trimite ieșirea
?>

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

