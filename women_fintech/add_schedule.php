<?php
ob_start(); // Start output buffering
require_once 'config/database.php';
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

// Verificăm dacă utilizatorul este autentificat și are rolul corespunzător,in caz contrat  utilizatorul este redirecționat către pagina de login
session_start();//Inițializează sesiunea PHP pentru accesarea variabilelor $_SESSION
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['mentor', 'member'])) {
    header("Location: login.php");
    exit();
}

// Procesăm formularul pentru crearea unei programări
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['schedule_session'])) {
    //codul de mai sus verifica dacă formularul a fost trimis (metoda HTTP este POST) și dacă butonul de trimitere cu numele schedule_session este setat
    $mentor_id = ($_SESSION['role'] === 'mentor') ? $_SESSION['user_id'] : $_POST['mentor_id'];
    $mentee_id = ($_SESSION['role'] === 'member') ? $_SESSION['user_id'] : $_POST['mentee_id'];
    $session_date = $_POST['session_date'];
    $session_time = $_POST['session_time'];
    //Dacă utilizatorul este un mentor, mentorul este utilizatorul curent.
    //Dacă utilizatorul este un membru, mentorul este selectat din formular.

    // Validare câmpuri
    //Se verifică dacă toate câmpurile sunt completate.
    //Se asigură că mentorul și mentee-ul nu sunt aceeași persoană.
    if (!empty($mentor_id) && !empty($mentee_id) && !empty($session_date) && !empty($session_time)) {
        if ($mentor_id === $mentee_id) {
            $message = "Mentorul și mentee-ul nu pot fi aceleași persoane!";
        } else {
            $partner_id = ($_SESSION['role'] === 'mentor') ? $mentee_id : $mentor_id;
            $partnerQuery = "SELECT CONCAT(first_name, ' ', last_name) AS full_name FROM members WHERE id = :partner_id";
            $partnerStmt = $db->prepare($partnerQuery);
            $partnerStmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
            $partnerStmt->execute();
            $partner = $partnerStmt->fetch(PDO::FETCH_ASSOC);
            //obtinerea numelui partenerului
            if ($partner) {
                $partner_name = $partner['full_name'];
                //mai jos salvam programarea in baza de date
                $query = "INSERT INTO scheduled_sessions (mentor_id, mentee_id, session_date, session_time) 
                          VALUES (:mentor_id, :mentee_id, :session_date, :session_time)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':mentor_id', $mentor_id);
                $stmt->bindParam(':mentee_id', $mentee_id);
                $stmt->bindParam(':session_date', $session_date);
                $stmt->bindParam(':session_time', $session_time);

                //daca inserarea programarii este reusita atunci vom insera o notificare in tabala notification
                if ($stmt->execute()) {
                    $notificationQuery = "INSERT INTO notifications (member_id, message) VALUES (:member_id, :message)";
                    $notificationStmt = $db->prepare($notificationQuery);
                    $notificationStmt->bindParam(':member_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $successMessage = "Programarea cu $partner_name a fost efectuată cu succes!";
                    $notificationStmt->bindParam(':message', $successMessage, PDO::PARAM_STR);
                    $notificationStmt->execute();

                    $_SESSION['successMessage'] = $successMessage;
                    header("Location: add_schedule.php");
                    exit();
                } else {
                    $message = "Eroare la adăugarea programării. Vă rugăm să încercați din nou.";
                }
            } else {
                $message = "Partenerul selectat nu a fost găsit!";
            }
        }
    } else {
        $message = "Toate câmpurile sunt obligatorii!";
    }
}

// Pregătim și executăm interogarea pentru a obține membri pentru dropdown-ul formularului
$userRole = $_SESSION['role'];
$queryMembers = $userRole === 'mentor' ?
    "SELECT id, first_name, last_name FROM members WHERE role = 'member' AND id != :user_id" :
    "SELECT id, first_name, last_name FROM members WHERE role = 'mentor' AND id != :user_id";

$stmtMembers = $db->prepare($queryMembers);
$stmtMembers->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmtMembers->execute();
$members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);
?>



<?php if (isset($message)): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form action="add_schedule.php" method="POST" class="form-container-events">
    <h2>Adaugă o Programare</h2>
    <label for="partner_id"><?php echo $userRole === 'mentor' ? 'Selectează membru:' : 'Selectează mentor:'; ?></label>
    <select id="partner_id" name="<?php echo $userRole === 'mentor' ? 'mentee_id' : 'mentor_id'; ?>" required>
        <option value="" disabled selected>-- Selectează --</option>
        <?php foreach ($members as $member): ?>
            <option value="<?php echo $member['id']; ?>">
                <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="session_date">Data programării:</label>
    <input type="date" id="session_date" name="session_date" required>

    <label for="session_time">Ora programării:</label>
    <input type="time" id="session_time" name="session_time" required>
    <input type="submit" name="schedule_session" class="btn-primary" value="Programează">
</form>

<p><a href="track_progress.php">Vezi lista programărilor</a></p>

<?php include_once "includes/footer.php"; ?>
<?php ob_end_flush(); ?>

<!-- Toastr Resources -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Afișăm toastr dacă există un mesaj de succes
        <?php if (isset($_SESSION['successMessage'])): ?>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
        };
        toastr.success("<?php echo $_SESSION['successMessage']; ?>");
        <?php unset($_SESSION['successMessage']); ?>
        <?php endif; ?>
    });

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