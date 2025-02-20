<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women FinTech</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Amarante&family=Faculty+Glyphic&display=swap');
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo.png" alt="Women in FinTech Logo" class="logo"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Profil</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="scheduleDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Programări
                    </a>
                    <div class="dropdown-menu" aria-labelledby="scheduleDropdown">
                        <a class="dropdown-item" href="add_schedule.php">Adaugă programare</a>
                        <a class="dropdown-item" href="track_progress.php">Listă programări</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="eventsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Evenimente
                    </a>
                    <div class="dropdown-menu" aria-labelledby="eventsDropdown">
                        <a class="dropdown-item" href="add_event.php">Adaugă eveniment</a>
                        <a class="dropdown-item" href="events.php">Listă evenimente</a>
                        <a class="dropdown-item" href="calendar.php">Calendar evenimente</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="resourcesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Resurse
                    </a>
                    <div class="dropdown-menu" aria-labelledby="resourcesDropdown">
                        <a class="dropdown-item" href="add_resource.php">Adaugă resursă</a>
                        <a class="dropdown-item" href="resources.php">Listă resurse</a>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="jobsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Joburi
                    </a>
                    <div class="dropdown-menu" aria-labelledby="resourcesDropdown">
                        <a class="dropdown-item" href="add_job.php">Adaugă job</a>
                        <a class="dropdown-item" href="jobs.php">Listă joburi</a>
                    </div>
                <li class="nav-item">
                    <a class="nav-link" href="members.php">Membri</a>
                </li>
                </li>
            </ul>
        </div>
        <div class="search-container">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="Caută membri...">
            </form>
        </div>

        <a class="nav-link" href="add_member.php">Înregistrare</a>
        <a class="nav-link" href="login.php">Login</a>
        <a class="nav-link" href="logout.php">Logout</a>
        <button id="theme-toggle" class="btn btn-secondary btn-sm ml-auto">
            🌙
        </button>

    </div>
</nav>
<div class="container mt-4">