<?php
// Pornim sesiunea
session_start();

// Asigură-te că nu există ieșiri accidentale înainte de header()
ob_start();

include_once "includes/header.php";  // Include header-ul
require_once('config/database.php');

// Verificare autentificare
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$userRole = $_SESSION['role'];

// Conectare la baza de date
$database = new Database();
$db = $database->getConnection();

// Obținerea informațiilor utilizatorului
$query = "SELECT * FROM members WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Profilul nu a fost găsit!";
    exit();
}

// Obținem programările utilizatorului (ca mentor sau mentee)
$querySessions = "
    SELECT 
        ss.session_date, 
        ss.session_time, 
        m.first_name AS partner_first_name, 
        m.last_name AS partner_last_name, 
        m.role AS partner_role
    FROM scheduled_sessions ss
    JOIN members m ON 
        (m.id = ss.mentor_id AND ss.mentee_id = :user_id) OR 
        (m.id = ss.mentee_id AND ss.mentor_id = :user_id)
    WHERE ss.mentor_id = :user_id OR ss.mentee_id = :user_id
    ORDER BY ss.session_date ASC, ss.session_time ASC";

$stmtSessions = $db->prepare($querySessions);
$stmtSessions->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmtSessions->execute();
$sessions = $stmtSessions->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container mt-4">
    <div class="profile-card">
        <div class="profile-content-container">
            <div class="profile-text-container">
                <!-- Afișează poza de profil (dacă există) -->
                <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="img-thumbnail" width="150">
                <?php endif; ?>

                <!-- Afișează informațiile utilizatorului -->
                <p><strong>Nume:</strong> <?php echo htmlspecialchars($user['first_name']) . ' ' . htmlspecialchars($user['last_name']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Profesie:</strong> <?php echo htmlspecialchars($user['profession']); ?></p>
                <p><strong>Companie:</strong> <?php echo htmlspecialchars($user['company']); ?></p>
                <p><strong>Expertiză:</strong> <?php echo nl2br(htmlspecialchars($user['expertise'])); ?></p>
                <p><strong>LinkedIn:</strong> <a href="<?php echo htmlspecialchars($user['linkedin_profile']); ?>" target="_blank">Vizualizează Profilul</a></p>

                <h3>Lista mea de evenimente</h3>
                <?php
                $query = "SELECT e.id, e.title FROM event_registrations er
                          JOIN events e ON er.event_id = e.id
                          WHERE er.member_id = :member_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':member_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($events): ?>
                    <ul>
                        <?php foreach ($events as $event): ?>
                            <li><a href="event_details.php?id=<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nu te-ai înscris la niciun eveniment încă.</p>
                <?php endif; ?>

                <h3>Lista mea de programări</h3>
                <?php if ($sessions): ?>
                    <ul>
                        <?php foreach ($sessions as $session): ?>
                            <li>
                                <strong>Data:</strong> <?php echo htmlspecialchars($session['session_date']); ?>,
                                <strong>Ora:</strong> <?php echo htmlspecialchars($session['session_time']); ?>
                                <br>
                                <strong>Partener:</strong>
                                <?php echo htmlspecialchars($session['partner_first_name']) . ' ' . htmlspecialchars($session['partner_last_name']); ?>
                                (<?php echo htmlspecialchars($session['partner_role'] === 'mentor' ? 'Mentor' : 'Mentee'); ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nu aveți programări înregistrate.</p>
                <?php endif; ?>

                <h3>Lista de joburi la care am aplicat</h3>
                <?php
                $query = "SELECT j.id, j.title 
                          FROM job_applications ja
                          JOIN jobs j ON ja.job_id = j.id
                          WHERE ja.member_id = :member_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':member_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($jobs): ?>
                    <ul>
                        <?php foreach ($jobs as $job): ?>
                            <li><a href="job_details.php?id=<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Nu ai aplicat la niciun job încă.</p>
                <?php endif; ?>
                <br>

                <!-- Butoane pentru a modifica profilul sau a te deconecta -->
                <a href="edit_profile.php?id=<?php echo $_SESSION['user_id']; ?>" class="btn btn-primary">Modifică Profilul</a>

            </div>
        </div>
    </div>
</div>

<?php
include_once "includes/footer.php";  // Include footer-ul
// Încheiem buffer-ul de ieșire
ob_end_flush();
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Verifică dacă utilizatorul are o preferință de temă salvată
        if (localStorage.getItem('theme') === 'dark') {
            body.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️';  // Icona pentru tema deschisă
        }

        // Schimbă tema la apăsarea butonului
        themeToggle.addEventListener('click', () => {
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '🌙';  // Icona pentru tema închisă
            } else {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '☀️';  // Icona pentru tema deschisă
            }
        });
    });
</script>
