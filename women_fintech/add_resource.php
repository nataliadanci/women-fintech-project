<?php
session_start();
require_once 'config/database.php'; // Include fișierul de configurare PDO

// Verificare rol utilizator (admin sau mentor)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'mentor'])) {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

// Obține conexiunea la baza de date
$database = new Database();
$pdo = $database->getConnection();

// Adăugare resurse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $file_url = !empty($_POST['file_url']) ? trim($_POST['file_url']) : null;
    $language = !empty($_POST['language']) ? trim($_POST['language']) : null;

    // Validare câmpuri
    if (empty($title) || empty($description) || empty($category)) {
        echo "Toate câmpurile obligatorii trebuie completate!";
    } else {
        try {
            // Interogare pentru inserare în baza de date
            $query = $pdo->prepare("
                INSERT INTO resources (title, description, category, file_url, author, language) 
                VALUES (:title, :description, :category, :file_url, :author, :language)
            ");
            $query->execute([
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'file_url' => $file_url,
                'author' =>$author,
                'language' =>$language
            ]);

            echo "Resursa a fost adăugată cu succes!";
            header("Location: resources.php");
            exit();
        } catch (PDOException $e) {
            echo "Eroare: " . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="form-container">
    <h2>Adaugă o resursă</h2>
    <form method="post">
        <div class="form-group">
            <label>Titlu:</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Autor:</label>
            <input type="text" name="author" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Descriere (text articol):</label>
            <input type="text" name="description" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Selectează categoria:</label>
            <select name="category" class="form-control">
                <option value="article">Articol</option>
                <option value="video">Video</option>
                <option value="podcast">Podcast</option>
                <option value="download">Descărcabile</option>
            </select>
        </div>

        <div class="form-group">
            <label>Limbă:</label>
            <input type="text" name="language" class="form-control" required>
        </div>

        <div class="form-group">
            <label>URL resursă (opțional):</label>
            <input type="url" name="file_url" class="form-control">
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
