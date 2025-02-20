<?php
session_start(); // Start the session at the very beginning

require_once "config/database.php";

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $job_id = $_GET['id'];

    $query = "SELECT * FROM jobs WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $job_id, PDO::PARAM_INT);
    $stmt->execute();
    $jobs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jobs) {
        echo "Resursa nu a fost găsită!";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $job_level = $_POST['job_level'];

    $query = "UPDATE jobs SET 
                title = :title, 
                description = :description,
                category = :category,
                company = :company,
                location = :location,
                salary = :salary,
                job_level = :job_level
              WHERE id = :id";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':company', $company);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':salary', $salary);
    $stmt->bindParam(':job_level', $job_level);
    $stmt->bindParam(':id', $job_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: jobs.php");
        exit();
    } else {
        echo "A apărut o eroare la actualizarea jobului!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Editează un Job</title>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="form-container">
    <h3>Editează un Job</h3>
    <form method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($jobs['id']); ?>">
        <div class="form-group">
            <label>Titlu:</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($jobs['title']); ?>" required>
        </div>

        <div class="form-group">
            <label>Selectează nivelul</label>
            <select name="job_level" class="form-control">
                <option value="internship" <?php echo ($jobs['job_level'] == 'internship') ? 'selected' : ''; ?>>Intership</option>
                <option value="entry-level" <?php echo ($jobs['job_level'] == 'entry_level') ? 'selected' : ''; ?>>Entry-level</option>
                <option value="mid-level" <?php echo ($jobs['job_level'] == 'mid_level') ? 'selected' : ''; ?>>Mid-level</option>
                <option value="senior" <?php echo ($jobs['job_level'] == 'senior') ? 'selected' : ''; ?>>Senior</option>
                <option value="manager" <?php echo ($jobs['job_level'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
            </select>
        </div>

        <div class="form-group">
            <label>Companie:</label>
            <input type="text" name="company" class="form-control" value="<?php echo htmlspecialchars($jobs['company']); ?>" required>
        </div>

        <div class="form-group">
            <label>Descriere:</label>
            <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($jobs['description']); ?>" required>
        </div>

        <div class="form-group">
            <label>Selectează categoria</label>
            <select name="category" class="form-control">
                <option value="it" <?php echo ($jobs['category'] == 'it') ? 'selected' : ''; ?>>IT</option>
                <option value="hr" <?php echo ($jobs['category'] == 'hr') ? 'selected' : ''; ?>>HR</option>
                <option value="finance" <?php echo ($jobs['category'] == 'finance') ? 'selected' : ''; ?>>Financiar</option>
                <option value="marketing" <?php echo ($jobs['category'] == 'marketing') ? 'selected' : ''; ?>>Marketing</option>
            </select>
        </div>

        <div class="form-group">
            <label>Locație:</label>
            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($jobs['location']); ?>" required>
        </div>

        <div class="form-group">
            <label>Salariu (opțional, în RON):</label>
            <input type="number" name="salary" class="form-control" value="<?php echo htmlspecialchars($jobs['salary']); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Salvează</button>
    </form>
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
</body>
</html>
