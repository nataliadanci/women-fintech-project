<?php
include_once "includes/header.php";
?>
<div class="jumbotron">
    <h1 class="display-4">Bine ați venit la Women FinTech!</h1>
    <p class="lead">Susținem femeile în domeniul tehnologiei financiare prin comunitate și colaborare.</p>
    <hr class="my-4">
    <p>Alăturați-vă comunității noastre de femei profesioniste din domeniul FinTech.</p>
    <a class="btn btn-primary btn-lg" href="add_member.php" role="button">Înscrie-te</a>
</div>
<?php
include_once "includes/footer.php";
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Verifică dacă utilizatorul are o preferință salvată
        if (localStorage.getItem('theme') === 'dark') {
            body.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️';
        }

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
