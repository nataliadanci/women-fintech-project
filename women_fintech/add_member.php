<?php
ob_start(); // Începe tamponarea ieșirii pentru a preveni erorile cu antetele HTTP

include_once "config/database.php";
include_once "includes/header.php";
include_once "upload.php";

// Verifică dacă utilizatorul este deja logat, și dacă da, redirecționează
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $profilePicturePath = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $uploadResult = uploadProfilePicture($_FILES['profile_picture']);
        if (strpos($uploadResult, 'uploads/') !== false) {
            $profilePicturePath = $uploadResult;
        } else {
            echo "<p>$uploadResult</p>"; // Eroare la încărcare fișier
        }
    }

    // Verificăm dacă username-ul sau email-ul există deja
    $queryCheck = "SELECT COUNT(*) FROM members WHERE email = ? OR username = ?";
    $stmtCheck = $db->prepare($queryCheck);
    $stmtCheck->execute([$_POST['email'], $_POST['username']]);
    if ($stmtCheck->fetchColumn() > 0) {
        echo "<p>Email-ul sau username-ul există deja!</p>";
        exit;
    }

    $query = "INSERT INTO members 
              (first_name, last_name, email, username, password, role, profession, company, expertise, linkedin_profile, profile_picture) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);

    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['username'],
        $hashedPassword,
        $_POST['role'] ?? 'member',
        $_POST['profession'],
        $_POST['company'],
        $_POST['expertise'],
        $_POST['linkedin_profile'],
        $profilePicturePath
    ]);

    // După ce datele au fost salvate cu succes, redirecționăm către pagina de login
    header("Location: login.php");
    exit(); // Important pentru a opri procesul ulterior
}

ob_end_flush(); // Trimite orice ieșire care a fost tamponată
?>

<div class="form-container">
    <h2>Înregistrare</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Prenume</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Nume</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Parolă</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Rol</label>
            <select name="role" class="form-control">
                <option value="member">Membru</option>
                <option value="mentor">Mentor</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label>Profesie</label>
            <input type="text" name="profession" class="form-control">
        </div>

        <div class="form-group">
            <label>Companie</label>
            <input type="text" name="company" class="form-control">
        </div>

        <div class="form-group">
            <label>Expertiză</label>
            <textarea name="expertise" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Profil LinkedIn</label>
            <input type="url" name="linkedin_profile" class="form-control">
        </div>

        <div class="form-group">
            <label>Poză profil</label>
            <input type="file" name="profile_picture" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Înregistrare</button>
    </form>
</div>

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
