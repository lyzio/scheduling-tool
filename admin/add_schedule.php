<?php
require_once '../admin/config.php';
include '../admin/header.php';  // Inkludera menyn

// Hämta rum från databasen
$rooms_query = "SELECT * FROM rooms";
$rooms_result = $conn->query($rooms_query);
$rooms = [];
if ($rooms_result->num_rows > 0) {
    while ($room = $rooms_result->fetch_assoc()) {
        $rooms[] = $room;
    }
}

// Hämta kategorier från databasen
$categories_query = "SELECT * FROM categories";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result->num_rows > 0) {
    while ($category = $categories_result->fetch_assoc()) {
        $categories[] = $category;
    }
}

// Hämta inställningar från databasen för att få tillgängliga datumintervall
$settings_query = "SELECT setting_value FROM settings WHERE setting_name = 'available_dates'";
$result = $conn->query($settings_query);

// Kontrollera om några datum finns tillgängliga
$available_dates = [];
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $available_dates = explode(',', $row['setting_value']); // Anta att datumen är sparade som en kommaseparerad sträng
}

// Hantera formulärinmatning
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date']; // Uppdaterat för att använda valt datum från knapparna
    $event_time = $_POST['event_time'];
    $end_time = $_POST['end_time'];
    $category_id = $_POST['category']; // Uppdaterat för att använda category_id
    $room_id = $_POST['room']; // Ändrad från 'room' till 'room_id'
    $event_link = $_POST['event_link'];
    $description = $_POST['description'];

    // Skapa SQL-sats för att lägga till post med category_id
    $sql = "INSERT INTO schedule (event_name, event_date, event_time, end_time, category_id, room_id, event_link, description) 
            VALUES ('$event_name', '$event_date', '$event_time', '$end_time', '$category_id', '$room_id', '$event_link', '$description')";

    // Kör SQL-satsen
    if ($conn->query($sql) === TRUE) {
        echo "<p>New record created successfully</p>";
    } else {
        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
    }
}

$conn->close(); // Stäng anslutningen
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Schedule</title>
    <link rel="stylesheet" href="../admin/admin-style.css">
</head>
<body>
<div class="container">
    <h1>Add New Event to Schedule</h1>

    <!-- Visa tillgängliga datumvalsknappar -->
    <div>
        <label for="event_date">Select Date:</label>
        <?php foreach ($available_dates as $date): ?>
            <button type="button" class="date-button" onclick="selectDate('<?php echo $date; ?>')"><?php echo $date; ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Formulär för att lägga till schema -->
    <form method="POST" action="add_schedule.php">
        <input type="hidden" id="event_date" name="event_date" value="">
        <!-- Resten av dina formulärfält -->
        <label for="event_name">Event Name:</label>
        <input type="text" id="event_name" name="event_name" required>

        <!-- Ytterligare formulärfält -->
        <label for="event_time">Start Time:</label>
        <input type="time" id="event_time" name="event_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" id="end_time" name="end_time" required>

        <label for="category">Category:</label>
        <select id="category" name="category">
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="room">Room:</label>
        <select id="room" name="room">
            <?php foreach ($rooms as $room): ?>
                <option value="<?php echo htmlspecialchars($room['id']); ?>">
                    <?php echo htmlspecialchars($room['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="event_link">Event Link:</label>
        <input type="url" id="event_link" name="event_link">

        <label for="description">Description:</label>
        <textarea id="description" name="description"></textarea>

        <input type="submit" value="Add Event">
    </form>
</div>

<script>
    // JavaScript-funktion för att uppdatera det dolda datumfältet
    function selectDate(date) {
        console.log("Selected date: " + date); // Felsökningsmeddelande
        document.getElementById('event_date').value = date;
    }
</script>
</body>
</html>
