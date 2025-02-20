<?php
session_start(); // Start the session at the very beginning

require_once "config/database.php";
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

// Verificăm dacă există un ID de job în URL
if (isset($_GET['id'])) {
    $job_id = $_GET['id'];

    // Obținem detalii despre eveniment
    $query = "SELECT * FROM jobs WHERE id = :job_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
    $stmt->execute();
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    // Dacă jobul nu există
    if (!$job) {
        echo "Jobul nu a fost găsit!";
        exit();
    }
} else {
    echo "ID-ul jobului nu a fost furnizat!";
    exit();
}
?>

<h2>Detalii job</h2>
<h3><?php echo htmlspecialchars($job['title']); ?></h3>
<strong>Domeniu:</strong> <?= htmlspecialchars($job['category']); ?>
<p><strong>Nivel:</strong> <?= htmlspecialchars($job['job_level']); ?></p>
<p><strong>Locație:</strong> <?= htmlspecialchars($job['location']); ?></p>
<p><strong>Companie:</strong> <?= htmlspecialchars($job['company']); ?></p>
<p><strong>Descriere:</strong> <?= htmlspecialchars($job['description']); ?></p>
<p><strong>Salariu (RON):</strong> <?= htmlspecialchars($job['salary']); ?></p>
<p><strong>Dată postare:</strong> <?= htmlspecialchars(date('Y-m-d', strtotime($job['created_at']))); ?></p>

<button class="btn-primary" onclick="window.location.href='jobs.php';">Înapoi la lista cu Joburi</button>

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
