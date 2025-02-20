<?php
ob_start(); // Activează output buffering

session_start(); // Inițiază sesiunea

require_once "config/database.php";
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

// Verificăm dacă există un ID de resursă în URL
if (isset($_GET['id'])) {
    $resource_id = $_GET['id'];

    // Obținem detalii despre resursă
    $query = "SELECT * FROM resources WHERE id = :resource_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':resource_id', $resource_id, PDO::PARAM_INT);
    $stmt->execute();
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dacă resursa nu există
    if (!$resource) {
        echo "Resursa nu a fost găsită!";
        exit();
    }
} else {
    echo "ID-ul resursei nu a fost furnizat!";
    exit();
}
?>

<h2>Detalii resursă</h2>
<h3><?php echo htmlspecialchars($resource['title']); ?></h3>
<p><strong>Autor:</strong> <?= htmlspecialchars($resource['author']); ?></p>
<p><strong>Descriere:</strong> <?= htmlspecialchars($resource['description']); ?></p>
<p><strong>Tip resursa:</strong> <?= htmlspecialchars($resource['category']); ?></p>
<p><strong>Limbă:</strong> <?= htmlspecialchars($resource['language']); ?></p>
<p><strong>URL resursa:</strong> <a href="<?= htmlspecialchars($resource['file_url']); ?>" target="_blank"><?= htmlspecialchars($resource['file_url']); ?></a></p>

<button class="btn-primary" onclick="window.location.href='resources.php';">Înapoi la lista cu resurse</button>

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
