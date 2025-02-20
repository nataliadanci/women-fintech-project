<?php
ob_start(); // Activează output buffering
session_start();
require_once "config/database.php";
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

// Verificare rol utilizator (admin sau mentor)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'mentor'])) {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

if (isset($_GET['id'])) {
    $resource_id = $_GET['id'];

    // Obține detaliile resursei
    $query = "SELECT * FROM resources WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $resource_id, PDO::PARAM_INT);
    $stmt->execute();
    $resources = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resources) {
        echo "Resursa nu a fost găsită!";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resource_id = $_POST['id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $file_url = $_POST['file_url'];
    $language = $_POST['language'];

    $query = "UPDATE resources SET 
                title = :title, 
                description = :description,
                category = :category,
                file_url = :file_url,
                author = :author,
                language = :language
              WHERE id = :id";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':file_url', $file_url);
    $stmt->bindParam(':language', $language);
    $stmt->bindParam(':id', $resource_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Resursă actualizată cu succes!";
        header("Location: resources.php");
        exit();
    } else {
        echo "A apărut o eroare la actualizarea resursei.";
    }
}
?>

<form method="POST" action="" class="form-container">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($resources['id']); ?>">
    <h3>Editează resursa</h3>
    <div class="form-group">
        <label>Titlu:</label>
        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($resources['title']); ?>" required>
    </div>

    <div class="form-group">
        <label>Autor:</label>
        <input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($resources['author']); ?>" required>
    </div>

    <div class="form-group">
        <label>Descriere (text articol):</label>
        <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($resources['description']); ?>" required>
    </div>

    <div class="form-group">
        <label>Selectează categoria:</label>
        <select name="category" class="form-control" required>
            <option value="article" <?php echo ($resources['category'] == 'article') ? 'selected' : ''; ?>>Articol</option>
            <option value="video" <?php echo ($resources['category'] == 'video') ? 'selected' : ''; ?>>Video</option>
            <option value="podcast" <?php echo ($resources['category'] == 'podcast') ? 'selected' : ''; ?>>Podcast</option>
            <option value="download" <?php echo ($resources['category'] == 'download') ? 'selected' : ''; ?>>Descărcabile</option>
        </select>
    </div>

    <div class="form-group">
        <label>Limbă:</label>
        <input type="text" name="language" class="form-control" value="<?php echo htmlspecialchars($resources['language']); ?>" required>
    </div>

    <div class="form-group">
        <label>URL resursă (opțional):</label>
        <input type="url" name="file_url" class="form-control" value="<?php echo htmlspecialchars($resources['file_url']); ?>">
    </div>

    <button type="submit" class="btn btn-primary">Salvează</button>
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

<?php ob_end_flush(); // Finalizează output buffering ?>
