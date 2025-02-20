<?php
include_once "config/database.php";
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

// Verificăm dacă este transmis un ID valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='error-message'>Membrul nu a fost găsit.</div>";
    include_once "includes/footer.php";
    exit();
}

// Preluăm detaliile membrului pe baza ID-ului
$memberId = (int)$_GET['id'];
$query = "SELECT * FROM members WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $memberId, PDO::PARAM_INT);
$stmt->execute();

// Verificăm dacă membrul există
$member = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$member) {
    echo "<div class='error-message'>Membrul nu a fost găsit.</div>";
    include_once "includes/footer.php";
    exit();
}
?>

<div class="container">
    <div class="profile-card">
        <div class="content-container">
            <div class="text-container">
                <h2><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h2>
                <p class="profession"><strong>Profesie:</strong> <?php echo htmlspecialchars($member['profession']); ?></p>
                <p><strong>Companie:</strong> <?php echo htmlspecialchars($member['company']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
                <p><strong>Rol:</strong> <?php echo ucfirst(htmlspecialchars($member['role'])); ?></p>
                <p><strong>Data înregistrării:</strong> <?php echo htmlspecialchars($member['created_at']); ?></p>
            </div>
            <img class="profile-picture" src="<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="Profile Picture">
        </div>
        <div class="button-container">
            <a href="members.php" class="btn-secondary">Înapoi</a>
        </div>
    </div>
</div>

<?php
include_once "includes/footer.php";
?>
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
