<?php
session_start();
// Distruge toate variabilele din sesiune
session_unset();
// Distruge sesiunea
session_destroy();
// Redirecționează utilizatorul la pagina de login
header("Location: login.php");
exit();
?>
