<?php
require 'config.php'; // Inkludera databasconfigurationsfilen
include 'header.php'; // Inkludera den gemensamma header-filen

// Hantera borttagning av schemaposter
if (isset($_GET['delete'])) {
    $schedule_id = $_GET['delete'];

    // Förberedd fråga för att ta bort schemapost
    $stmt = $conn->prepare("DELETE FROM schedule WHERE id = ?");
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        echo "Record deleted successfully.";
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
}

// Hämta alla schemaposter från databasen för att visa
$schedule_result = $conn->query("SELECT schedule.*, categories.name AS category_name, rooms.name AS room_name FROM schedule 
                                 JOIN categories ON schedule.category_id = categories.id
                                 JOIN rooms ON schedule.room_id = rooms.id
                                 ORDER BY event_date, event_time");

$schedule = [];
while ($row = $schedule_result->fetch_assoc()) {
    $schedule[] = $row;
}

$conn->close(); // Stäng anslutningen
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Schedule</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Innehållscontainer -->
    <div class="container">
        <header>
            <h1>Manage Schedule</h1>
        </header>

        <!-- Lista över schemaposter -->
        <h2>Existing Schedule</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Category</th>
                    <th>Room</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedule as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['id']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_name']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_time']); ?></td>
                        <td><?php echo htmlspecialchars($event['end_time']); ?></td>
                        <td><?php echo htmlspecialchars($event['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($event['room_name']); ?></td>
                        <td>
                            <a href="edit_schedule.php?id=<?php echo htmlspecialchars($event['id']); ?>">Edit</a> | 
                            <a href="manage_schedule.php?delete=<?php echo htmlspecialchars($event['id']); ?>" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Sidfot -->
    <footer>
        <p>Made by <a href="http://lyzio.net" target="_blank">Oliver</a></p>
    </footer>
</body>
</html>
