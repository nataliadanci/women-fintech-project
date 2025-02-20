<?php
ob_start(); // Activează output buffering
session_start(); // Start the session at the very beginning

require_once "config/database.php";

// Check if user is logged in and get their member ID
if (!isset($_SESSION['member_id'])) {
    echo "Trebuie să fii autentificat pentru a aplica la un job!";
    exit();
}
$member_id = $_SESSION['member_id'];

include_once "includes/header.php"; // Asigură-te că acest fișier nu produce output înainte de codul PHP

// Get the database connection
$database = new Database();
$pdo = $database->getConnection();

// Check if a job ID is provided
if (!isset($_GET['id'])) {
    echo "ID-ul jobului nu a fost furnizat!";
    exit();
}

$job_id = $_GET['id'];

// Get job details
$query = "SELECT * FROM jobs WHERE id = :job_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
$stmt->execute();
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "Jobul nu a fost găsit!";
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicant_name = $_POST['applicant_name'];
    $applicant_email = $_POST['applicant_email'];
    $short_description = $_POST['short_description'];
    $github_profile = $_POST['github_profile'] ?? null;

    // Handle CV upload
    $cv = $_FILES['cv'];
    $cv_path = 'uploads/' . basename($cv['name']);
    move_uploaded_file($cv['tmp_name'], $cv_path);

    // Handle cover letter upload
    $cover_letter = $_FILES['cover_letter'];
    $cover_letter_path = 'uploads/' . basename($cover_letter['name']);
    move_uploaded_file($cover_letter['tmp_name'], $cover_letter_path);

    // Insert application into database
    $query = "INSERT INTO job_applications (job_id, member_id, applicant_name, applicant_email, cv_path, cover_letter_path, short_description, github_profile) 
              VALUES (:job_id, :member_id, :applicant_name, :applicant_email, :cv_path, :cover_letter_path, :short_description, :github_profile)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
    $stmt->bindParam(':member_id', $member_id, PDO::PARAM_INT);
    $stmt->bindParam(':applicant_name', $applicant_name);
    $stmt->bindParam(':applicant_email', $applicant_email);
    $stmt->bindParam(':cv_path', $cv_path);
    $stmt->bindParam(':cover_letter_path', $cover_letter_path);
    $stmt->bindParam(':short_description', $short_description);
    $stmt->bindParam(':github_profile', $github_profile);

    if ($stmt->execute()) {
        header("Location: jobs.php");
        exit();
    } else {
        echo "A apărut o eroare la aplicare.";
    }
}
?>

<h2>Aplică pentru job: <?php echo htmlspecialchars($job['title']); ?></h2>

<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label>Nume (complet):</label>
        <input type="text" name="applicant_name" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Email:</label>
        <input type="email" name="applicant_email" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Descriere scurtă:</label>
        <textarea name="short_description" class="form-control" required></textarea>
    </div>

    <div class="form-group">
        <label>Link GitHub (opțional):</label>
        <input type="url" name="github_profile" class="form-control">
    </div>

    <div class="form-group">
        <label>CV:</label>
        <input type="file" name="cv" class="form-control" required>
    </div>

    <div class="form-group">
        <label>Scrisoare de intenție:</label>
        <input type="file" name="cover_letter" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Aplică</button>
</form>

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
