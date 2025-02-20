<?php
ob_start(); // Buffer pentru a preveni ieșirea accidentală
session_start(); // Start the session
include_once "config/database.php";
include_once "includes/header.php";
include_once "upload.php"; // Include fișierul pentru încărcarea fișierului

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: denied_access.php");
    exit(); // Stop script execution
}

$database = new Database();
$db = $database->getConnection();


// Dacă formularul este trimis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificăm dacă username-ul introdus nu este deja folosit de alt utilizator
    $queryCheckUsername = "SELECT COUNT(*) FROM members WHERE username = ? AND id != ?";
    $stmtCheckUsername = $db->prepare($queryCheckUsername);
    $stmtCheckUsername->execute([$_POST['username'], $_GET['id']]);

    if ($stmtCheckUsername->fetchColumn() > 0) {
        $_SESSION['error'] = "Username-ul există deja!";
        header("Location: edit_member.php?id=" . $_GET['id']);
        exit();
    }

    // Verifică și încarcă imaginea de profil
    $profilePicturePath = $_POST['existing_profile_picture']; // Păstrează vechea imagine dacă nu se încarcă o imagine nouă
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $uploadResult = uploadProfilePicture($_FILES['profile_picture']);
        if (strpos($uploadResult, 'uploads/') !== false) {
            $profilePicturePath = $uploadResult; // Actualizează calea fișierului
        } else {
            $_SESSION['error'] = $uploadResult; // Salvează eroarea la încărcare
            header("Location: edit_member.php?id=" . $_GET['id']);
            exit();
        }
    }

    // Verificăm dacă utilizatorul a introdus o parolă nouă
    $passwordQueryPart = '';
    $passwordParams = [];
    if (!empty($_POST['password'])) {
        // Dacă există o parolă nouă, o hash-uim și o adăugăm în interogare
        $passwordQueryPart = ", password = ?";
        $passwordParams[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // Actualizează informațiile despre membru în baza de date
    $query = "UPDATE members 
              SET first_name=?, last_name=?, email=?, username=?, profession=?, company=?, expertise=?, linkedin_profile=?, profile_picture=? $passwordQueryPart 
              WHERE id=?";
    $params = [
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['email'],
        $_POST['username'],
        $_POST['profession'],
        $_POST['company'],
        $_POST['expertise'],
        $_POST['linkedin_profile'],
        $profilePicturePath,
        $_GET['id']
    ];

    // Dacă a fost introdusă o parolă nouă, o adăugăm la parametrii interogării
    if (!empty($_POST['password'])) {
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $stmt = $db->prepare($query);
    if ($stmt->execute($params)) {
        // Redirecționează utilizatorul după actualizare
        $_SESSION['success'] = "Membrul a fost actualizat cu succes!";
        header("Location: members.php");
        exit();
    } else {
        $_SESSION['error'] = "Eroare la actualizare!";
        header("Location: edit_member.php?id=" . $_GET['id']);
        exit();
    }
}

// Obține datele membrului
$query = "SELECT * FROM members WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_GET['id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

// Dacă membrul nu există, redirecționează
if (!$member) {
    $_SESSION['error'] = "Membrul nu a fost găsit!";
    header("Location: members.php");
    exit();
}
?>

<div class="form-container" enctype="multipart/form-data">
    <h3>Editează membrul</h3>
    <?php
    // Afișează mesaje de eroare sau succes
    if (isset($_SESSION['error'])) {
        echo "<p style='color: red;'>" . htmlspecialchars($_SESSION['error']) . "</p>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green;'>" . htmlspecialchars($_SESSION['success']) . "</p>";
        unset($_SESSION['success']);
    }
    ?>
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
            <input type="password" name="password" class="form-control" placeholder="Lasă gol pentru a păstra parola actuală">
        </div>

        <div class="form-group">
            <label>Rol:</label>
            <select name="role" class="form-control" disabled>
                <option value="member" <?php echo $member['role'] == 'member' ? 'selected' : ''; ?>>Membru</option>
                <option value="mentor" <?php echo $member['role'] == 'mentor' ? 'selected' : ''; ?>>Mentor</option>
                <option value="admin" <?php echo $member['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
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
            <label for="profile_picture">Poză profil:</label>
            <input type="file" name="profile_picture" class="form-control">
            <?php if ($member['profile_picture']): ?>
                <div>
                    <img src="<?php echo $member['profile_picture']; ?>" alt="Profile Picture" width="100">
                </div>
                <input type="hidden" name="existing_profile_picture" value="<?php echo $member['profile_picture']; ?>">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Actualizează</button>
    </form>
</div>

<?php
include_once "includes/footer.php";
ob_end_flush(); // Închide buffer-ul și trimite ieșirea
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
