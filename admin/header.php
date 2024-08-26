<?php
require 'config.php'; // Inkludera databasconfigurationsfilen
session_start(); // Starta sessionen

// Kontrollera om användaren redan är inloggad via en kaka
if (!isset($_COOKIE['loggedin']) || $_COOKIE['loggedin'] != true) {
    // Om användaren inte är inloggad, omdirigera till login.php
    header('Location: login.php');
    exit;
}

// Om användaren är inloggad, sätt sessionen
$_SESSION['loggedin'] = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="menu-style.css"> <!-- Koppla CSS-filen -->
</head>
<body>
    <div class="header">
        <div class="menu">
            <a href="manage_schedule.php">Manage Schedule</a>
            <a href="add_schedule.php">Add Schedule</a>
            <a href="manage_categories.php">Manage Categories</a>
            <a href="manage_rooms.php">Manage Rooms</a>
            <a href="settings.php">Settings</a>
            <a href="../index.php">Go to Schedule</a>
        </div>
        <div class="logout">
            <a href="logout.php">Logga ut</a>
        </div>
    </div>
</body>
</html>
