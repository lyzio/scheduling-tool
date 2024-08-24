<?php
session_start(); // Starta sessionen

// Kontrollera om användaren är inloggad
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
?>

<!-- Navigeringsmeny -->
<nav>
    <ul>
        <li><a href="manage_schedule.php">Manage Schedule</a></li>
        <li><a href="add_schedule.php">Add Schedule</a></li>
        <li><a href="manage_categories.php">Manage Categories</a></li>
        <li><a href="manage_rooms.php">Manage Rooms</a></li>
        <li><a href="settings.php">Settings</a></li> <!-- Ny länk för inställningar -->
        <li style="margin-left: auto;"><a href="../index.php">Gå till schemat</a></li> <!-- Ny länk för att gå till schemat -->
    </ul>
</nav>
