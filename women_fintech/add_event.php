<?php
require_once 'config/database.php';
include_once "includes/header.php";


$database = new Database();
$db = $database->getConnection();


// Procesarea formularului pentru adăugarea unui eveniment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $is_online = isset($_POST['is_online']) ? 1 : 0; // 1 pentru evenimente online, 0 pentru offline

    // Validăm datele
    if (!empty($title) && !empty($event_date)) {
        // Pregătim interogarea pentru a adăuga evenimentul în baza de date
        $query = "INSERT INTO events (title, description, event_date, location, is_online) 
                  VALUES (:title, :description, :event_date, :location, :is_online)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':is_online', $is_online);

        if ($stmt->execute()) {
            $message = "Eveniment adăugat cu succes!";
        } else {
            $message = "A apărut o eroare la adăugarea evenimentului.";
        }
    } else {
        $message = "Te rugăm să completezi toate câmpurile obligatorii.";
    }
}

?>



<!-- Mesaj de succes sau eroare pentru adăugarea evenimentului -->
<?php if (isset($message)): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<form action="add_event.php" method="POST" class="form-container-events">
    <h2>Adaugă un eveniment</h2>
    <label for="title">Titlul evenimentului:</label>
    <input type="text" id="title" name="title" required>

    <label for="description">Descriere:</label>
    <textarea id="description" name="description"></textarea>

    <label for="event_date">Data evenimentului:</label>
    <input type="datetime-local" id="event_date" name="event_date" required>

    <label for="location">Locația:</label>
    <input type="text" id="location" name="location">

    <label for="is_online">Eveniment online:</label>
    <input type="checkbox" id="is_online" name="is_online">

    <button id="add-event-btn" type="submit" name="add_event">Adaugă eveniment</button>
    <br>
    <br>
    <a href="events.php">Înapoi la lista evenimentelor</a>
</form>



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
