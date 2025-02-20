<?php
// Include fișierul de configurare al bazei de date
require_once "config/database.php";

// Crearea unei instanțe a obiectului Database
$database = new Database();
$db = $database->getConnection();

// Definirea interogării SQL pentru a obține evenimentele
$query = "SELECT id, title, event_date, is_online, location FROM events ORDER BY event_date ASC";
$stmt = $db->prepare($query);
$stmt->execute();

// Crearea unui array pentru evenimente
$events = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $event = [
        'id' => $row['id'],                          // ID-ul evenimentului
        'title' => $row['title'],                    // Titlul evenimentului
        'start' => $row['event_date'],               // Data de început
        'url' => "event_details.php?id=" . $row['id'], // URL pentru detalii
    ];

    // Adăugarea evenimentului în array
    $events[] = $event;
}

// Setarea header-ului pentru a răspunde cu date JSON
header('Content-Type: application/json');

// Răspunsul către FullCalendar sub formă de JSON
echo json_encode($events);
?>
