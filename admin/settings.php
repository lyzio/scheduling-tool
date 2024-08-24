<?php
require 'config.php'; // Inkludera databasconfigurationsfilen
include 'header.php'; // Inkludera den gemensamma header-filen

// Hantera formulärinmatning för att spara datum och tidsintervall
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_dates'])) {
        $dates = array_filter(array_map('trim', explode(',', $_POST['available_dates'])));
        $dates_string = implode(',', $dates);

        // Spara valda datum i databasen
        $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES ('available_dates', ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("ss", $dates_string, $dates_string);
        $stmt->execute();
        $stmt->close();

        echo "Dates updated successfully.";
    }

    if (isset($_POST['save_time_range'])) {
        $start_time = $_POST['time_range_start'];
        $end_time = $_POST['time_range_end'];

        // Spara starttiden i databasen
        $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES ('time_range_start', ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("ss", $start_time, $start_time);
        $stmt->execute();
        $stmt->close();

        // Spara sluttiden i databasen
        $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES ('time_range_end', ?) 
                                ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("ss", $end_time, $end_time);
        $stmt->execute();
        $stmt->close();

        echo "Time range updated successfully.";
    }
}

// Hämta valda datum från databasen
$available_dates = [];
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'available_dates'");
if ($row = $result->fetch_assoc()) {
    $available_dates = explode(',', $row['setting_value']);
}

// Hämta tidsintervallinställningar från databasen
$start_time = '07:00'; // Standardvärde
$end_time = '24:00';   // Standardvärde

$result = $conn->query("SELECT * FROM settings WHERE setting_name IN ('time_range_start', 'time_range_end')");
while ($row = $result->fetch_assoc()) {
    if ($row['setting_name'] === 'time_range_start') {
        $start_time = $row['setting_value'];
    } elseif ($row['setting_name'] === 'time_range_end') {
        $end_time = $row['setting_value'];
    }
}

$conn->close(); // Stäng anslutningen
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Innehållscontainer -->
    <div class="container">
        <header>
            <h1>Settings</h1>
        </header>

        <!-- Formulär för att välja vilka datum som ska visas -->
        <form method="POST" action="">
            <h2>Manage Available Dates</h2>
            <label for="available_dates">Enter dates (YYYY-MM-DD) separated by commas:</label>
            <textarea id="available_dates" name="available_dates" rows="4"><?php echo htmlspecialchars(implode(', ', $available_dates)); ?></textarea>
            <button type="submit" name="save_dates">Save Dates</button>
        </form>

        <!-- Formulär för att välja tidsintervall -->
        <form method="POST" action="">
            <h2>Set Time Range for Schedule</h2>
            <label for="time_range_start">Start Time:</label>
            <input type="time" id="time_range_start" name="time_range_start" value="<?php echo htmlspecialchars($start_time); ?>" required>

            <label for="time_range_end">End Time:</label>
            <input type="time" id="time_range_end" name="time_range_end" value="<?php echo htmlspecialchars($end_time); ?>" required>

            <button type="submit" name="save_time_range">Save Time Range</button>
        </form>

        <!-- Formulär för att ladda upp logotyp -->
        <form method="POST" action="" enctype="multipart/form-data">
            <h2>Upload Logo</h2>
            <label for="logo">Select image to upload:</label>
            <input type="file" name="logo" id="logo" required>
            <button type="submit" name="upload_logo">Upload Logo</button>
        </form>
    </div>

    <!-- Sidfot -->
    <footer>
        <p>Made by <a href="http://lyzio.net" target="_blank">Oliver</a></p>
    </footer>
</body>
</html>
