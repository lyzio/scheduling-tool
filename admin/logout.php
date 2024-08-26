<?php
// Starta sessionen om den inte redan är startad
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ta bort alla session-variabler
$_SESSION = array();

// Förstör sessionen
session_destroy();

// Ta bort session-cookien om den finns
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Ta bort den specifika 'loggedin' kakan
setcookie('loggedin', '', time() - 3600, "/"); // Samma path som vid skapandet

// Omdirigera till startsidan
header("Location: ../index.php");
exit();
?>
