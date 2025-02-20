<?php
ob_start(); // Activează output buffering
require_once 'config/database.php';
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Obține detaliile evenimentului
    $query = "SELECT * FROM events WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo "Evenimentul nu a fost găsit!";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];  // Folosim doar un câmp pentru data evenimentului
    $location = $_POST['location'];
    $is_online = isset($_POST['is_online']) ? 1 : 0;

    $query = "UPDATE events SET 
                title = :title, 
                description = :description, 
                event_date = :event_date,  -- Actualizăm doar event_date
                location = :location, 
                is_online = :is_online 
              WHERE id = :id";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':event_date', $event_date);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':is_online', $is_online, PDO::PARAM_INT);
    $stmt->bindParam(':id', $event_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: events.php");
        exit();
    } else {
        echo "A apărut o eroare la actualizarea evenimentului.";
    }
}
?>

<form method="POST" action="" class="form-container-events">
    <h3>Editează evenimentul</h3>
    <label for="title">Titlu:</label>
    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>

    <label for="description">Descriere:</label>
    <textarea id="description" name="description"><?php echo htmlspecialchars($event['description']); ?></textarea>

    <label for="event_date">Data evenimentului:</label>
    <input type="datetime-local" id="event_date" name="event_date" value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>

    <label for="location">Locație:</label>
    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>">

    <label for="is_online">Eveniment online:</label>
    <input type="checkbox" id="is_online" name="is_online" <?php echo $event['is_online'] ? 'checked' : ''; ?>>

    <button id="edit-event-btn" type="submit">Salvează</button>
</form>

<?php include_once "includes/footer.php"; ?>
<?php ob_end_flush(); ?>


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
