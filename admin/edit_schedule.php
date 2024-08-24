<?php
require 'config.php'; // Inkludera databasconfigurationsfilen
include 'header.php'; // Inkludera den gemensamma header-filen

// Hämta schedule-posten för redigering
if (isset($_GET['id'])) {
    $schedule_id = $_GET['id'];

    // Förberedd fråga för att hämta schemainformation baserat på id
    $stmt = $conn->prepare("SELECT * FROM schedule WHERE id = ?");
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "No record found.";
        exit;
    }
    $stmt->close();
}

// Hantera formulärinmatning för att uppdatera posten
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $schedule_id = $_POST['schedule_id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $end_time = $_POST['end_time'];
    $category_id = $_POST['category'];
    $room_id = $_POST['room']; // Uppdaterad för att använda room_id
    $event_link = $_POST['event_link'];
    $description = $_POST['description'];

    // Förberedd fråga för att uppdatera schemapost
    $stmt = $conn->prepare("UPDATE schedule SET event_name=?, event_date=?, event_time=?, end_time=?, category_id=?, room_id=?, event_link=?, description=? WHERE id=?");
    $stmt->bind_param("ssssisssi", $event_name, $event_date, $event_time, $end_time, $category_id, $room_id, $event_link, $description, $schedule_id);

    if ($stmt->execute()) {
        echo "Record updated successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
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
    <title>Edit Schedule</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Innehållscontainer -->
    <div class="container">
        <header>
            <h1>Edit Schedule</h1>
        </header>

        <?php if (isset($row)): ?>
            <form method="POST" action="">
                <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($row['id']); ?>">

                <label for="event_name">Event Name:</label>
                <input type="text" id="event_name" name="event_name" value="<?php echo htmlspecialchars($row['event_name']); ?>" required>

                <label for="event_date">Event Date:</label>
                <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($row['event_date']); ?>" required>

                <label for="event_time">Start Time:</label>
                <input type="time" id="event_time" name="event_time" value="<?php echo htmlspecialchars($row['event_time']); ?>" required>

                <label for="end_time">End Time:</label>
                <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($row['end_time']); ?>" required>

                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php if ($cat['id'] == $row['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="room">Room:</label>
                <select id="room" name="room" required>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo htmlspecialchars($room['id']); ?>" <?php if ($room['id'] == $row['room_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($room['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="event_link">Event Link:</label>
                <input type="url" id="event_link" name="event_link" value="<?php echo htmlspecialchars($row['event_link']); ?>">

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>

                <button type="submit" name="update">Update Event</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Sidfot -->
    <footer>
        <p>Made by <a href="http://lyzio.net" target="_blank">Oliver</a></p>
    </footer>
</body>
</html>
