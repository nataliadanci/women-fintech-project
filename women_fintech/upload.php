<?php
// upload.php

function uploadProfilePicture($file) {
    $targetDir = "uploads/";
    $fileName = basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Verifică dacă fișierul este o imagine
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return "Fișierul nu este o imagine.";
    }

    // Verifică dimensiunea fișierului (ex: max 2MB)
    if ($file["size"] > 2 * 1024 * 1024) {
        return "Fișierul este prea mare.";
    }

    // Permite doar anumite tipuri de fișiere
    $allowedFileTypes = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedFileTypes)) {
        return "Doar fișiere JPG, JPEG, PNG și GIF sunt permise.";
    }

    // Verifică dacă fișierul există deja
    if (file_exists($targetFile)) {
        return "Fișierul există deja.";
    }

    // Încărcă fișierul
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return $targetFile;
    } else {
        return "Eroare la încărcarea fișierului.";
    }
}
?>
