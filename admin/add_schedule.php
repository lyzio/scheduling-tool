<?php
require 'config.php'; // Inkludera databasconfigurationsfilen
include 'header.php'; // Inkludera den gemensamma header-filen

// Hantera formulärinmatning för att lägga till ett nytt schema
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_schedule'])) {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $end_time = $_POST['end_time'];
    $category_id = $_POST['category'];
    $room_id = $_POST['room'];  // Uppdaterad för att använda room_id
    $event_link = $_POST['event_link'];
    $description = $_POST['description'];

    // Förberedd fråga för att lägga till ny schemapost
    $stmt = $conn->prepare("INSERT INTO schedule (event_name, event_date, event_time, end_time, category_id, room_id, event_link, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisss", $event_name, $event_date, $event_time, $end_time, $category_id, $room_id, $event_link, $description);

    if ($stmt->execute()) {
        echo "New schedule added successfully.";
    } else {
        echo "Error adding schedule: " . $conn->error;
    }

    $stmt->close();
}

// Hämta alla kategorier från kategoritabellen för att visa i formuläret
$categories_result = $conn->query("SELECT * FROM categories");
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}

// Hämta alla aktiva rum från rooms-tabellen för att visa i formuläret
$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_active = TRUE");
$rooms = [];
while ($room = $rooms_result->fetch_assoc()) {
    $rooms[] = $room;
}

$conn->close(); // Stäng anslutningen
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Schedule</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Innehållscontainer -->
    <div class="container">
        <header>
            <h1>Add New Schedule</h1>
        </header>

        <!-- Formulär för att lägga till nytt schema -->
        <form method="POST" action="">
            <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" required>

            <label for="event_date">Event Date:</label>
            <input type="date" id="event_date" name="event_date" required>

            <label for="event_time">Start Time:</label>
            <input type="time" id="event_time" name="event_time" required>

            <label for="end_time">End Time:</label>
            <input type="time" id="end_time" name="end_time" required>

            <label for="category">Category:</label>
            <select id="category" name="category" required>
                <option value="">Select a Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="room">Room:</label>
            <select id="room" name="room" required>
                <option value="">Select a Room</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?php echo htmlspecialchars($room['id']); ?>">
                        <?php echo htmlspecialchars($room['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="event_link">Event Link:</label>
            <input type="url" id="event_link" name="event_link">

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4"></textarea>

            <button type="submit" name="add_schedule">Add Schedule</button>
        </form>
    </div>

    <!-- Sidfot -->
    <footer>
        <p>Made by <a href="http://lyzio.net" target="_blank">Oliver</a></p>
    </footer>
</body>
</html>
