<?php
session_start();
require_once 'config/database.php'; // Include fișierul de configurare PDO

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

// Obține conexiunea la baza de date
$database = new Database();
$pdo = $database->getConnection();

// Adăugare job
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $company = trim($_POST['company']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $location = trim($_POST['location']);
    $salary = !empty($_POST['salary']) ? trim($_POST['salary']) : null;
    $job_level = trim($_POST['job_level']);

    // Validare câmpuri
    if (empty($title) || empty($description) || empty($category) || empty($company) || empty($location)) {
        echo "Toate câmpurile obligatorii trebuie completate!";
    } else {
        try {
            // Interogare pentru inserare în baza de date
            $query = $pdo->prepare("
                INSERT INTO jobs (title, description, category, company, location, salary, job_level) 
                VALUES (:title, :description, :category, :company, :location, :salary, :job_level)
            ");
            $query->execute([
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'company' => $company,
                'location' => $location,
                'salary' => $salary,
                'job_level' => $job_level
            ]);

            echo "Jobul a fost adăugat cu succes!";
            header("Location: jobs.php");
            exit();
        } catch (PDOException $e) {
            echo "Eroare: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Adaugă Job</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="form-container">
    <h2>Adaugă un Job</h2>
    <form method="post">
        <div class="form-group">
            <label>Titlu:</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Selectează nivelul</label>
            <select name="job_level" class="form-control">
                <option value="internship">Intership</option>
                <option value="entry-level">Entry-level</option>
                <option value="mid-level">Mid-level</option>
                <option value="senior">Senior</option>
                <option value="manager">Manager</option>
            </select>
        </div>

        <div class="form-group">
            <label>Companie:</label>
            <input type="text" name="company" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Descriere:</label>
            <textarea name="description" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label>Selectează categoria</label>
            <select name="category" class="form-control">
                <option value="it">IT</option>
                <option value="hr">HR</option>
                <option value="finance">Financiar</option>
                <option value="marketing">Marketing</option>
            </select>
        </div>

        <div class="form-group">
            <label>Locație:</label>
            <input type="text" name="location" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Salariu (opțional, în RON):</label>
            <input type="number" name="salary" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Adaugă</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
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
</body>
</html>
