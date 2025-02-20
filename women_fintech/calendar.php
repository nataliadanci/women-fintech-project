<?php
require_once 'config/database.php';
include_once "includes/header.php";

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM events ORDER BY event_date ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$eventsForCalendar = [];
foreach ($events as $event) {
    $eventsForCalendar[] = [
        'title' => htmlspecialchars($event['title']),
        'start' => $event['event_date'],
        'url'   => "event_details.php?id=" . $event['id'],
    ];
}
?>

<h2>Calendarul Evenimentelor</h2>

<!-- Div pentru calendar -->
<div id="calendar"></div>

<!-- FullCalendar CSS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<!-- FullCalendar JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.7/main.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: <?php echo json_encode($eventsForCalendar); ?>,
            locale: 'ro',
            eventClick: function (info) {
                window.location.href = info.event.url;
                info.jsEvent.preventDefault();
            }
        });

        calendar.render();
    });
</script>

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
