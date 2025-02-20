<?php
include_once "includes/header.php";
?>

    <div class="container">
        <h1>Acces interzis! Ne pare rău :(</h1>
        <p>Nu aveți permisiunea de a accesa această pagină.</p>
        <a href="index.php">Înapoi la pagina principală</a>
    </div>

<?php
include_once "includes/footer.php";
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
