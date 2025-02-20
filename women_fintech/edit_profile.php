<?php
ob_start(); // Previne problemele cu anteturile trimise
session_start();

include_once "config/database.php";
include_once "includes/header.php";
include_once "upload.php";

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    echo "Acces interzis! Trebuie să fii autentificat pentru a edita profilul.";
    exit();
}

$user_id = $_SESSION['user_id'];  // ID-ul utilizatorului logat
$role = $_SESSION['role'] ?? '';  // Verifică rolul utilizatorului din sesiune

$database = new Database();
$db = $database->getConnection();

// Verificăm dacă se editează un alt profil (în cazul în care admin-ul editează alți membri)
if (isset($_GET['edit_id']) && $role == 'admin') {
    $edit_id = $_GET['edit_id'];
} else {
    // Dacă nu este admin, nu poate edita decât propriul profil
    $edit_id = $user_id;
}

// Dacă formularul este trimis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Păstrează vechea imagine, dacă nu este încărcată una nouă
    $profilePicturePath = $_POST['existing_profile_picture'] ?? '';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        // Încărcare fișier imagine de profil
        $uploadResult = uploadProfilePicture($_FILES['profile_picture']);
        if (strpos($uploadResult, 'uploads/') !== false) {
            $profilePicturePath = $uploadResult;  // Actualizează calea fișierului
        } else {
            echo "<p>$uploadResult</p>";  // Eroare la încărcarea fișierului
        }
    }

    // Verificăm dacă email-ul sau username-ul există deja, excluzând ID-ul utilizatorului curent
    $queryCheck = "SELECT COUNT(*) FROM members WHERE (email = ? OR username = ?) AND id != ?";
    $stmtCheck = $db->prepare($queryCheck);
    $stmtCheck->execute([$_POST['email'], $_POST['username'], $edit_id]);
    if ($stmtCheck->fetchColumn() > 0) {
        echo "<p>Email-ul sau username-ul există deja!</p>";
        exit();
    }

    // Actualizează informațiile despre membru în baza de date
    $query = "UPDATE members 
              SET first_name=?, last_name=?, email=?, username=?, password=?, role=?, profession=?, company=?, expertise=?, linkedin_profile=?, profile_picture=? 
              WHERE id=?";
    $stmt = $db->prepare($query);

    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);  // Criptarea parolei

    // Execută actualizarea datelor în baza de date
    if ($stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['username'],
        $hashedPassword,  // Actualizare parolă
        $_POST['role'] ?? 'member',  // Setare rol default
        $_POST['profession'],
        $_POST['company'],
        $_POST['expertise'],
        $_POST['linkedin_profile'],
        $profilePicturePath,  // Calea fișierului de profil
        $edit_id  // ID-ul utilizatorului editat
    ])) {
        // Redirecționează utilizatorul către pagina sa de profil
        header("Location: profile.php");
        exit();
    } else {
        echo "Eroare la actualizarea profilului.";
    }
}

// Obține datele membrului pentru a le preîncărca în formular
$query = "SELECT * FROM members WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$edit_id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifică dacă se poate edita un alt profil
if ($role != 'admin' && $user_id != $edit_id) {
    echo "Acces interzis! Nu ai permisiunea de a edita acest profil.";
    exit();
}

?>

<div class="form-container" enctype="multipart/form-data">
    <h2>Editează profil</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Prenume:</label>
            <input type="text" name="first_name" class="form-control"
                   value="<?php echo htmlspecialchars($member['first_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Nume:</label>
            <input type="text" name="last_name" class="form-control"
                   value="<?php echo htmlspecialchars($member['last_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo htmlspecialchars($member['email']); ?>" required>
        </div>

        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" class="form-control"
                   value="<?php echo htmlspecialchars($member['username']); ?>" required>
        </div>

        <div class="form-group">
            <label>Parolă:</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Rol:</label>
            <?php if ($role == 'admin'): ?>
                <select name="role" class="form-control">
                    <option value="member" <?php if ($member['role'] == 'member') echo 'selected'; ?>>Membru</option>
                    <option value="mentor" <?php if ($member['role'] == 'mentor') echo 'selected'; ?>>Mentor</option>
                    <option value="admin" <?php if ($member['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            <?php else: ?>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['role']); ?>" disabled>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Profesie:</label>
            <input type="text" name="profession" class="form-control"
                   value="<?php echo htmlspecialchars($member['profession']); ?>">
        </div>

        <div class="form-group">
            <label>Companie:</label>
            <input type="text" name="company" class="form-control"
                   value="<?php echo htmlspecialchars($member['company']); ?>">
        </div>

        <div class="form-group">
            <label>Expertiză:</label>
            <textarea name="expertise" class="form-control"><?php echo htmlspecialchars($member['expertise']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Profil LinkedIn:</label>
            <input type="url" name="linkedin_profile" class="form-control"
                   value="<?php echo htmlspecialchars($member['linkedin_profile']); ?>">
        </div>

        <div class="form-group">
            <label>Poză profil:</label>
            <input type="file" name="profile_picture" class="form-control">
            <?php if ($member['profile_picture']): ?>
                <div>
                    <img src="<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="Profile Picture" width="100">
                </div>
                <input type="hidden" name="existing_profile_picture" value="<?php echo htmlspecialchars($member['profile_picture']); ?>">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Actualizează</button>
    </form>
</div>

<?php
include_once "includes/footer.php";
ob_end_flush(); // Închide buffer-ul
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
