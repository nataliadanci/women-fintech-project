<?php
session_start();
ob_start();
require_once 'config/database.php';
require_once 'includes/header.php';

// Verificare autentificare
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit(); // Oprește scriptul după redirecționare
}

$userRole = $_SESSION['role'];

// Inițializarea conexiunii la baza de date
$database = new Database();
$db = $database->getConnection();

// Query-uri pentru dashboard
$totalMembersQuery = "SELECT COUNT(*) as total FROM members";
$totalMembers = $db->query($totalMembersQuery)->fetch(PDO::FETCH_ASSOC)['total'];

// Admin-specific queries
if ($userRole == 'admin') {
    $professionsQuery = "SELECT profession, COUNT(*) as count FROM members GROUP BY profession";
    $professionsResult = $db->query($professionsQuery);

    $topCompaniesQuery = "SELECT company, COUNT(*) as count FROM members GROUP BY company ORDER BY count DESC LIMIT 5";
    $topCompaniesResult = $db->query($topCompaniesQuery);


    // Total job listings
    $totalJobsQuery = "SELECT COUNT(*) as total FROM jobs";
    $totalJobs = $db->query($totalJobsQuery)->fetch(PDO::FETCH_ASSOC)['total'];

    // Applicants per job
    $applicantsPerJobQuery = "SELECT j.title, COUNT(ja.id) as applicants_count FROM jobs j
                          LEFT JOIN job_applications ja ON j.id = ja.job_id
                          GROUP BY j.id";
    $applicantsPerJobResult = $db->query($applicantsPerJobQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Applicants per month in the last 3 months
    $applicantsPerMonthQuery = "SELECT DATE_FORMAT(applied_at, '%Y-%m') as month, COUNT(*) as count
                            FROM job_applications
                            WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                            GROUP BY month
                            ORDER BY month DESC";
    $applicantsPerMonthResult = $db->query($applicantsPerMonthQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Total resources
    $totalResourcesQuery = "SELECT COUNT(*) as total FROM resources";
    $totalResources = $db->query($totalResourcesQuery)->fetch(PDO::FETCH_ASSOC)['total'];

    // Resources posted in the last 3 months
    $resourcesLast3MonthsQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                              FROM resources
                              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                              GROUP BY month";
    $resourcesLast3MonthsResult = $db->query($resourcesLast3MonthsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Resource distribution by type
    $resourceDistributionQuery = "SELECT category, COUNT(*) as count FROM resources GROUP BY category";
    $resourceDistributionResult = $db->query($resourceDistributionQuery)->fetchAll(PDO::FETCH_ASSOC);

}

// Mentor-specific queries
if ($userRole == 'mentor') {
    // Query for new members per month in the current year
    $newMembersQuery = "SELECT MONTH(created_at) as month, COUNT(*) as count 
                        FROM members 
                        WHERE YEAR(created_at) = YEAR(CURDATE()) 
                        GROUP BY MONTH(created_at)";
    $newMembersResult = $db->query($newMembersQuery);

    // Query for total events created by the mentor
    $mentorEventsQuery = "SELECT COUNT(*) as total 
                          FROM events 
                          WHERE created_by = :user_id";
    $stmtMentorEvents = $db->prepare($mentorEventsQuery);
    $stmtMentorEvents->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtMentorEvents->execute();
    $totalEvents = $stmtMentorEvents->fetch(PDO::FETCH_ASSOC)['total'];


// Inițializare variabile pentru a evita erorile
    $recommendedUsersByProfession = [];
    $recommendedUsersByCompany = [];
    $recommendedUsers = [];

// Obține profesia utilizatorului
    $professionQuery = "SELECT profession FROM members WHERE id = :user_id";
    $stmtProfession = $db->prepare($professionQuery);
    $stmtProfession->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtProfession->execute();
    $profession = $stmtProfession->fetch(PDO::FETCH_ASSOC)['profession'] ?? null;

// Obține compania utilizatorului
    $companyQuery = "SELECT company FROM members WHERE id = :user_id";
    $stmtCompany = $db->prepare($companyQuery);
    $stmtCompany->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtCompany->execute();
    $company = $stmtCompany->fetch(PDO::FETCH_ASSOC)['company'] ?? null;

// Recomandări pe profesie
    if ($profession) {
        $recommendationsQueryByProfession = "SELECT id, first_name, last_name, profession, company 
                                         FROM members 
                                         WHERE profession = :profession AND id != :user_id";
        $stmtRecommendationsByProfession = $db->prepare($recommendationsQueryByProfession);
        $stmtRecommendationsByProfession->bindParam(':profession', $profession, PDO::PARAM_STR);
        $stmtRecommendationsByProfession->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtRecommendationsByProfession->execute();
        $recommendedUsersByProfession = $stmtRecommendationsByProfession->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

// Recomandări pe companie
    if ($company) {
        $recommendationsQueryByCompany = "SELECT id, first_name, last_name, profession, company 
                                      FROM members 
                                      WHERE company = :company AND id != :user_id";
        $stmtRecommendationsByCompany = $db->prepare($recommendationsQueryByCompany);
        $stmtRecommendationsByCompany->bindParam(':company', $company, PDO::PARAM_STR);
        $stmtRecommendationsByCompany->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtRecommendationsByCompany->execute();
        $recommendedUsersByCompany = $stmtRecommendationsByCompany->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

// Combină recomandările din cele două surse
    $recommendedUsers = array_merge($recommendedUsersByProfession, $recommendedUsersByCompany);

// Elimină duplicatele
    $recommendedUsers = array_map("unserialize", array_unique(array_map("serialize", $recommendedUsers)));



    // Applicants per month in the last 3 months
    $applicantsPerMonthQuery = "SELECT DATE_FORMAT(applied_at, '%Y-%m') as month, COUNT(*) as count
                                FROM job_applications
                                WHERE applied_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                                GROUP BY month
                                ORDER BY month DESC";
    $applicantsPerMonthResult = $db->query($applicantsPerMonthQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Check if the result is valid before looping
    if ($applicantsPerMonthResult !== false) {
        foreach ($applicantsPerMonthResult as $applicantMonth) {
            // Process each month and count of applicants here
        }
    } else {
        echo "No applicants found in the last 3 months.";
    }

    // Resources posted in the last 3 months
    $resourcesLast3MonthsQuery = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                                  FROM resources
                                  WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                                  GROUP BY month";
    $resourcesLast3MonthsResult = $db->query($resourcesLast3MonthsQuery)->fetchAll(PDO::FETCH_ASSOC);

    // Check if the result is valid before looping
    if ($resourcesLast3MonthsResult !== false) {
        foreach ($resourcesLast3MonthsResult as $resourceMonth) {
            // Process each month and count of resources posted here
        }
    } else {
        echo "No resources found in the last 3 months.";
    }

    // Job recommendations based on profession
    $professionQuery = "SELECT profession FROM members WHERE id = :user_id";
    $stmtProfession = $db->prepare($professionQuery);
    $user_id = $_SESSION['user_id'];  // Explicitly define the variable
    $stmtProfession->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtProfession->execute();
    $profession = $stmtProfession->fetch(PDO::FETCH_ASSOC)['profession'];

    // Check if the profession is 'student' and fetch job recommendations accordingly
    if (strtolower($profession) == 'student') {
        // Recommend internships if the user is a student
        $jobRecommendationsQuery = "SELECT id, title, company FROM jobs WHERE job_level = 'internship'";
        $stmtJobRecommendations = $db->prepare($jobRecommendationsQuery);
        $stmtJobRecommendations->execute();
        $recommendedJobsByProfession = $stmtJobRecommendations->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Recommend jobs based on the profession
        $jobRecommendationsQuery = "SELECT id, title, company FROM jobs WHERE title LIKE :profession";
        $stmtJobRecommendations = $db->prepare($jobRecommendationsQuery);
        $professionLike = "%$profession%";  // Explicitly define the variable
        $stmtJobRecommendations->bindParam(':profession', $professionLike, PDO::PARAM_STR);
        $stmtJobRecommendations->execute();
        $recommendedJobsByProfession = $stmtJobRecommendations->fetchAll(PDO::FETCH_ASSOC);
    }

    // Job recommendations based on the company
    $companyQuery = "SELECT company FROM members WHERE id = :user_id";
    $stmtCompany = $db->prepare($companyQuery);
    $stmtCompany->bindParam(':user_id', $user_id, PDO::PARAM_INT);  // Reuse the explicit variable
    $stmtCompany->execute();
    $company = $stmtCompany->fetch(PDO::FETCH_ASSOC)['company'];

    $jobRecommendationsQueryCompany = "SELECT id, title, company FROM jobs WHERE company = :company";
    $stmtJobRecommendationsCompany = $db->prepare($jobRecommendationsQueryCompany);
    $stmtJobRecommendationsCompany->bindParam(':company', $company, PDO::PARAM_STR);
    $stmtJobRecommendationsCompany->execute();
    $recommendedJobsByCompany = $stmtJobRecommendationsCompany->fetchAll(PDO::FETCH_ASSOC);

    // Combine the job recommendations
    $allRecommendedJobs = array_merge($recommendedJobsByProfession, $recommendedJobsByCompany);

    // Obținerea listei de mentees recomandați
    $menteesQuery = "SELECT id, first_name, last_name, profession, company 
                     FROM members 
                     WHERE role = 'member' AND id != :user_id";
    $stmtMentees = $db->prepare($menteesQuery);
    $stmtMentees->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtMentees->execute();
    $recommendedMentees = $stmtMentees->fetchAll(PDO::FETCH_ASSOC);

    // Recomandări pe baza evenimentelor comune
    $mentorEventsQueryCommon = "SELECT e.id, e.title FROM event_registrations er
                               JOIN events e ON er.event_id = e.id
                               WHERE er.member_id = :user_id";
    $stmtEventsCommon = $db->prepare($mentorEventsQueryCommon);
    $stmtEventsCommon->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtEventsCommon->execute();
    $commonEvents = $stmtEventsCommon->fetchAll(PDO::FETCH_ASSOC);

    if (count($commonEvents) > 0) {
        // Generăm o listă de parametri pentru evenimentele comune
        $eventIds = array_column($commonEvents, 'id');
        $placeholders = implode(',', array_map(function ($key) { return ":event_id_$key"; }, array_keys($eventIds)));

        $recommendationsQueryEvents = "SELECT m.id, m.first_name, m.last_name, m.profession, m.company 
                                       FROM members m
                                       JOIN event_registrations er ON m.id = er.member_id
                                       WHERE er.event_id IN ($placeholders) AND m.id != :user_id";

        // Pregătim parametrii pentru interogare
        $params = [':user_id' => $_SESSION['user_id']];
        foreach ($eventIds as $key => $eventId) {
            $params[":event_id_$key"] = $eventId;
        }

        // Executăm interogarea cu parametrii
        $stmtRecommendationsEvents = $db->prepare($recommendationsQueryEvents);
        $stmtRecommendationsEvents->execute($params);
        $recommendedUsersByEvents = $stmtRecommendationsEvents->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $recommendedUsersByEvents = [];
    }

    // Combină recomandările din toate categoriile
    $allRecommendedUsers = array_merge($recommendedUsersByProfession, $recommendedUsersByCompany, $recommendedMentees, $recommendedUsersByEvents);
}



// Member-specific queries
if ($userRole == 'member') {
    // Obține evenimentele la care este înscris membrul
    $registeredEventsQuery = "SELECT e.id, e.title FROM event_registrations er
                              JOIN events e ON er.event_id = e.id
                              WHERE er.member_id = :member_id";
    $stmt = $db->prepare($registeredEventsQuery);
    $stmt->bindParam(':member_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $registeredEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inițializare variabile pentru a evita erorile
    $recommendedUsersByProfession = [];
    $recommendedUsersByCompany = [];
    $recommendedUsers = [];

// Obține profesia utilizatorului
    $professionQuery = "SELECT profession FROM members WHERE id = :user_id";
    $stmtProfession = $db->prepare($professionQuery);
    $stmtProfession->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtProfession->execute();
    $profession = $stmtProfession->fetch(PDO::FETCH_ASSOC)['profession'] ?? null;

// Obține compania utilizatorului
    $companyQuery = "SELECT company FROM members WHERE id = :user_id";
    $stmtCompany = $db->prepare($companyQuery);
    $stmtCompany->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtCompany->execute();
    $company = $stmtCompany->fetch(PDO::FETCH_ASSOC)['company'] ?? null;

// Recomandări pe profesie
    if ($profession) {
        $recommendationsQueryByProfession = "SELECT id, first_name, last_name, profession, company 
                                         FROM members 
                                         WHERE profession = :profession AND id != :user_id";
        $stmtRecommendationsByProfession = $db->prepare($recommendationsQueryByProfession);
        $stmtRecommendationsByProfession->bindParam(':profession', $profession, PDO::PARAM_STR);
        $stmtRecommendationsByProfession->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtRecommendationsByProfession->execute();
        $recommendedUsersByProfession = $stmtRecommendationsByProfession->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

// Recomandări pe companie
    if ($company) {
        $recommendationsQueryByCompany = "SELECT id, first_name, last_name, profession, company 
                                      FROM members 
                                      WHERE company = :company AND id != :user_id";
        $stmtRecommendationsByCompany = $db->prepare($recommendationsQueryByCompany);
        $stmtRecommendationsByCompany->bindParam(':company', $company, PDO::PARAM_STR);
        $stmtRecommendationsByCompany->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmtRecommendationsByCompany->execute();
        $recommendedUsersByCompany = $stmtRecommendationsByCompany->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

// Combină recomandările din cele două surse
    $recommendedUsers = array_merge($recommendedUsersByProfession, $recommendedUsersByCompany);

// Elimină duplicatele
    $recommendedUsers = array_map("unserialize", array_unique(array_map("serialize", $recommendedUsers)));

    // Recomandări pe baza evenimentelor comune
    if (count($registeredEvents) > 0) {
        // Generăm o listă de parametri pentru evenimente
        $eventIds = array_column($registeredEvents, 'id');
        $placeholders = implode(',', array_map(function ($key) { return ":event_id_$key"; }, array_keys($eventIds)));

        $recommendationsQueryEvents = "SELECT m.id, m.first_name, m.last_name, m.profession, m.company 
                                       FROM members m
                                       JOIN event_registrations er ON m.id = er.member_id
                                       WHERE er.event_id IN ($placeholders) AND m.id != :user_id";

        // Pregătim parametrii pentru interogare
        $params = [':user_id' => $_SESSION['user_id']];
        foreach ($eventIds as $key => $eventId) {
            $params[":event_id_$key"] = $eventId;
        }

        // Executăm interogarea cu parametrii
        $stmtRecommendationsEvents = $db->prepare($recommendationsQueryEvents);
        $stmtRecommendationsEvents->execute($params);
        $recommendedUsersByEvents = $stmtRecommendationsEvents->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $recommendedUsersByEvents = [];
    }

    // Combină recomandările din toate categoriile și elimină duplicatele
    $allRecommendedUsers = array_merge($recommendedUsersByProfession, $recommendedUsersByCompany, $recommendedUsersByEvents);
    // Elimină duplicatele pe baza ID-ului
    $allRecommendedUsers = array_map("unserialize", array_unique(array_map("serialize", $allRecommendedUsers)));

    // Obținerea listei de mentori recomandați

    $mentorsQuery = "SELECT id, first_name, last_name, profession, company 
                     FROM members 
                     WHERE role = 'mentor' AND id != :user_id";
    $stmtMentors = $db->prepare($mentorsQuery);
    $stmtMentors->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmtMentors->execute();
    $recommendedMentors = $stmtMentors->fetchAll(PDO::FETCH_ASSOC);

    // Job recommendations based on profession
    // Existing code to fetch profession
    $professionQuery = "SELECT profession FROM members WHERE id = :user_id";
    $stmtProfession = $db->prepare($professionQuery);
    $user_id = $_SESSION['user_id'];  // Explicitly define the variable
    $stmtProfession->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtProfession->execute();
    $profession = $stmtProfession->fetch(PDO::FETCH_ASSOC)['profession'];

    // Check if the profession is 'student' and fetch job recommendations accordingly
    if (strtolower($profession) == 'student') {
        // Recommend internships if the user is a student
        $jobRecommendationsQuery = "SELECT id, title, company FROM jobs WHERE job_level = 'internship'";
        $stmtJobRecommendations = $db->prepare($jobRecommendationsQuery);
        $stmtJobRecommendations->execute();
        $recommendedJobsByProfession = $stmtJobRecommendations->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Recommend jobs based on the profession
        $jobRecommendationsQuery = "SELECT id, title, company FROM jobs WHERE title LIKE :profession";
        $stmtJobRecommendations = $db->prepare($jobRecommendationsQuery);
        $professionLike = "%$profession%";  // Explicitly define the variable
        $stmtJobRecommendations->bindParam(':profession', $professionLike, PDO::PARAM_STR);
        $stmtJobRecommendations->execute();
        $recommendedJobsByProfession = $stmtJobRecommendations->fetchAll(PDO::FETCH_ASSOC);
    }

    // Job recommendations based on the company
    $companyQuery = "SELECT company FROM members WHERE id = :user_id";
    $stmtCompany = $db->prepare($companyQuery);
    $stmtCompany->bindParam(':user_id', $user_id, PDO::PARAM_INT);  // Reuse the explicit variable
    $stmtCompany->execute();
    $company = $stmtCompany->fetch(PDO::FETCH_ASSOC)['company'];

    $jobRecommendationsQueryCompany = "SELECT id, title, company FROM jobs WHERE company = :company";
    $stmtJobRecommendationsCompany = $db->prepare($jobRecommendationsQueryCompany);
    $stmtJobRecommendationsCompany->bindParam(':company', $company, PDO::PARAM_STR);
    $stmtJobRecommendationsCompany->execute();
    $recommendedJobsByCompany = $stmtJobRecommendationsCompany->fetchAll(PDO::FETCH_ASSOC);

    // Combine the job recommendations
    $allRecommendedJobs = array_merge($recommendedJobsByProfession, $recommendedJobsByCompany);
}
?>

<div class="dashboard">
    <!-- Secțiune Admin -->
    <?php if ($userRole == 'admin'): ?>
        <div class="admin-section">
            <h2>Statistici Admin</h2>
            <p><strong>Număr total de membri:</strong> <?php echo $totalMembers; ?></p>

            <h3>Distribuția pe profesii:</h3>
            <ul>
                <?php while ($row = $professionsResult->fetch(PDO::FETCH_ASSOC)): ?>
                    <li><?php echo $row['profession']; ?>: <?php echo $row['count']; ?> membri</li>
                <?php endwhile; ?>
            </ul>

            <h3>Top 5 companii reprezentate:</h3>
            <ul>
                <?php while ($row = $topCompaniesResult->fetch(PDO::FETCH_ASSOC)): ?>
                    <li><?php echo $row['company']; ?>: <?php echo $row['count']; ?> membri</li>
                <?php endwhile; ?>
            </ul>


            <p><strong>Număr total de joburi:</strong> <?php echo $totalJobs; ?></p>

            <h3>Număr de aplicații per job:</h3>
            <ul>
                <?php foreach ($applicantsPerJobResult as $job): ?>
                    <li><?php echo htmlspecialchars($job['title']); ?>: <?php echo $job['applicants_count']; ?> aplicanți</li>
                <?php endforeach; ?>
            </ul>

            <h3>Aplicații la joburi în ultimele 3 luni:</h3>
            <ul>
                <?php foreach ($applicantsPerMonthResult as $month): ?>
                    <li><?php echo $month['month']; ?>: <?php echo $month['count']; ?> aplicații</li>
                <?php endforeach; ?>
            </ul>

            <p><strong>Număr total de resurse: </strong><?php echo $totalResources; ?></p>

            <h3>Resurse postate în ultimele 3 luni:</h3>
            <ul>
                <?php foreach ($resourcesLast3MonthsResult as $month): ?>
                    <li><?php echo $month['month']; ?>: <?php echo $month['count']; ?> resurse noi</li>
                <?php endforeach; ?>
            </ul>

            <h3>Distribuția resurselor pe categorii:</h3>
            <ul>
                <?php foreach ($resourceDistributionResult as $resource): ?>
                    <li><?php echo htmlspecialchars($resource['category']); ?>: <?php echo $resource['count']; ?> resurse</li>
                <?php endforeach; ?>
            </ul>


        </div>
    <?php endif; ?>

    <!-- Secțiune Mentor -->
    <?php if ($userRole == 'mentor'): ?>
        <div class="mentor-section">
            <h3>Membri noi pe lună:</h3>
            <ul>
                <?php while ($row = $newMembersResult->fetch(PDO::FETCH_ASSOC)): ?>
                    <li>Luna <?php echo $row['month']; ?>: <?php echo $row['count']; ?> membri noi</li>
                <?php endwhile; ?>
            </ul>

            <h3>Resurse postate în ultimele 3 luni:</h3>
            <ul>
                <?php foreach ($resourcesLast3MonthsResult as $month): ?>
                    <li><?php echo $month['month']; ?>: <?php echo $month['count']; ?> resurse noi</li>
                <?php endforeach; ?>
            </ul>

            <h3>Aplicații la joburi în ultimele 3 luni:</h3>
            <ul>
                <?php foreach ($applicantsPerMonthResult as $month): ?>
                    <li><?php echo $month['month']; ?>: <?php echo $month['count']; ?> aplicații</li>
                <?php endforeach; ?>
            </ul>

            <h3>Recomandări de joburi:</h3>
            <?php if ($allRecommendedJobs): ?>
                <ul>
                    <?php foreach ($allRecommendedJobs as $job): ?>
                        <li><a href="job_details.php?id=<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></a> - <?php echo htmlspecialchars($job['company']); ?></li>                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nu există recomandări de joburi pentru tine.</p>
            <?php endif; ?>

            <h3>Recomandări de conexiuni:</h3>
            <?php if (!empty($recommendedUsers)): ?>
                <ul>
                    <?php foreach ($recommendedUsers as $user): ?>
                        <li>
                            <a href="member_details.php?id=<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </a>
                            - <?php echo htmlspecialchars($user['profession']); ?> la <?php echo htmlspecialchars($user['company']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nu există recomandări de conexiuni pentru tine.</p>
            <?php endif; ?>

            <h3>Mentees recomandați:</h3>
            <?php if ($recommendedMentees): ?>
                <ul>
                    <?php foreach ($recommendedMentees as $mentee): ?>
                        <li>
                            <a href="member_details.php?id=<?php echo $mentee['id']; ?>">
                                <?php echo htmlspecialchars($mentee['first_name'] . ' ' . $mentee['last_name']); ?>
                            </a>
                            - <?php echo htmlspecialchars($mentee['profession']); ?> la <?php echo htmlspecialchars($mentee['company']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nu există mentees recomandați pentru tine în acest moment.</p>
            <?php endif; ?>

        </div>
    <?php endif; ?>

    <!-- Secțiune Membru -->
    <?php if ($userRole == 'member'): ?>
        <div class="member-section">
            <h3>Evenimente la care te-ai înregistrat:</h3>
            <?php if ($registeredEvents): ?>
                <ul>
                    <?php foreach ($registeredEvents as $event): ?>
                        <li><a href="event_details.php?id=<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['title']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nu te-ai înscris la niciun eveniment încă.</p>
            <?php endif; ?>



            <h3>Lista de joburi la care am aplicat:</h3>
            <?php
            $query = "SELECT j.id, j.title 
          FROM job_applications ja
          JOIN jobs j ON ja.job_id = j.id
          WHERE ja.member_id = :member_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':member_id', $_SESSION['user_id'], PDO::PARAM_INT);  // Folosim user_id
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


            <h3>Recomandări de joburi:</h3>
            <?php if ($allRecommendedJobs): ?>
                <ul>
                    <?php foreach ($allRecommendedJobs as $job): ?>
                        <li><a href="job_details.php?id=<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['title']); ?></a> - <?php echo htmlspecialchars($job['company']); ?></li>                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nu există recomandări de joburi pentru tine.</p>
            <?php endif; ?>


            <h3>Recomandări de conexiuni:</h3>
            <?php if (!empty($recommendedUsers)): ?>
                <ul>
                    <?php foreach ($recommendedUsers as $user): ?>
                        <li>
                            <a href="member_details.php?id=<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </a>
                            - <?php echo htmlspecialchars($user['profession']); ?> la <?php echo htmlspecialchars($user['company']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nu există recomandări de conexiuni pentru tine.</p>
            <?php endif; ?>


            <h3>Mentori recomandați:</h3>
            <?php if ($recommendedMentors): ?>
                <ul>
                    <?php foreach ($recommendedMentors as $mentor): ?>
                        <li>
                            <a href="member_details.php?id=<?php echo $mentor['id']; ?>">
                                <?php echo htmlspecialchars($mentor['first_name'] . ' ' . $mentor['last_name']); ?>
                            </a>
                            - <?php echo htmlspecialchars($mentor['profession']); ?> la <?php echo htmlspecialchars($mentor['company']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Nu există mentori recomandați pentru tine în acest moment.</p>
            <?php endif; ?>

        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
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
