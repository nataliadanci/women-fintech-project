<?php
include_once "includes/header.php";
require_once('config/database.php'); // Importă fișierul de conexiune la baza de date

// Inițializează conexiunea la baza de date
$database = new Database();
$db = $database->getConnection();

// Setări pentru paginare
$itemsPerPage = 6; // Numărul de rezultate pe pagină
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Verifică dacă s-a primit un termen de căutare
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search = '%' . htmlspecialchars($_GET['query']) . '%'; // Adaugă wildcard pentru LIKE

    // Construiește interogarea pentru a afla numărul total de rezultate
    $countQuery = "
        SELECT COUNT(*) as total FROM members 
        WHERE first_name LIKE :search 
        OR last_name LIKE :search 
        OR profession LIKE :search
    ";
    $stmt = $db->prepare($countQuery);
    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    $stmt->execute();
    $totalResults = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalResults / $itemsPerPage);

    // Construiește interogarea SQL pentru căutare cu limită și offset
    $query = "
        SELECT * FROM members 
        WHERE first_name LIKE :search 
        OR last_name LIKE :search 
        OR profession LIKE :search
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    // Afișează rezultatele căutării
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        echo "<h2>Rezultate:</h2>";
        echo "<div class='row'>"; // Start the row for the card layout
        foreach ($results as $row) {
            ?>
            <div class="col-md-4">
                <div class="card member-card">
                    <div class="card-body">
                        <div class="content-container">
                            <div class="text-container">
                                <h5 class="card-title"><a href="member_details.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></a></h5>
                                <p class="card-text">
                                    <strong>Profesie:</strong> <?php echo htmlspecialchars($row['profession']); ?><br>
                                    <strong>Companie:</strong> <?php echo htmlspecialchars($row['company']); ?>
                                </p>
                            </div>
                            <img class="profile-picture" src="<?php echo !empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : 'default-profile.png'; ?>" alt="Profile Picture" width="100" height="100">
                        </div>
                        <div class="btnContainer">
                            <a href="member_details.php?id=<?php echo $row['id']; ?>">Detalii</a> |
                            <a href="edit_member.php?id=<?php echo $row['id']; ?>">Editează</a> |
                            <a href="delete_member.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Ești sigur că vrei să ștergi acest eveniment?');">Șterge</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        echo "</div>"; // End the row
    } else {
        echo "<p>No results found for '" . htmlspecialchars($_GET['query']) . "'</p>";
    }

    // Include paginarea
    ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?php echo $currentPage - 1; ?>&query=<?php echo urlencode($_GET['query']); ?>" class="prev">Înapoi</a>
        <?php endif; ?>

        <?php for ($page = 1; $page <= $totalPages; $page++): ?>
            <a href="?page=<?php echo $page; ?>&query=<?php echo urlencode($_GET['query']); ?>" class="<?php echo ($page == $currentPage) ? 'active' : ''; ?>">
                <?php echo $page; ?>
            </a>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?php echo $currentPage + 1; ?>&query=<?php echo urlencode($_GET['query']); ?>" class="next">Înainte</a>
        <?php endif; ?>
    </div>
    <?php
} else {
    echo "<p>Please enter a search term.</p>";
}

include_once "includes/footer.php";
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
