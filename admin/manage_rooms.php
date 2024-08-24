<?php
require 'config.php'; // Inkludera databasconfigurationsfilen
include 'header.php'; // Inkludera den gemensamma header-filen

// Hantera formulärinmatning för att lägga till, uppdatera eller ta bort rum
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_room'])) {
        $name = $_POST['name'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Förberedd fråga för att lägga till ett nytt rum
        $stmt = $conn->prepare("INSERT INTO rooms (name, is_active) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $is_active);

        if ($stmt->execute()) {
            echo "Room added successfully.";
        } else {
            echo "Error adding room: " . $conn->error;
        }

        $stmt->close();
    } elseif (isset($_POST['update_room'])) {
        $id = $_POST['room_id'];
        $name = $_POST['name'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Förberedd fråga för att uppdatera ett rum
        $stmt = $conn->prepare("UPDATE rooms SET name = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $is_active, $id);

        if ($stmt->execute()) {
            echo "Room updated successfully.";
        } else {
            echo "Error updating room: " . $conn->error;
        }

        $stmt->close();
    } elseif (isset($_POST['delete_room'])) {
        $id = $_POST['room_id'];

        // Förberedd fråga för att ta bort ett rum
        $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Room deleted successfully.";
        } else {
            echo "Error deleting room: " . $conn->error;
        }

        $stmt->close();
    }
}

// Hämta alla rum från rums-tabellen för att visa
$rooms_result = $conn->query("SELECT * FROM rooms");
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
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Innehållscontainer -->
    <div class="container">
        <header>
            <h1>Manage Rooms</h1>
        </header>

        <!-- Formulär för att lägga till nytt rum -->
        <form method="POST" action="">
            <label for="name">Room Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="is_active">Is Active:</label>
            <input type="checkbox" id="is_active" name="is_active" checked>

            <button type="submit" name="add_room">Add Room</button>
        </form>

        <!-- Lista över befintliga rum -->
        <h2>Existing Rooms</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Is Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($room['id']); ?></td>
                        <td><?php echo htmlspecialchars($room['name']); ?></td>
                        <td><?php echo $room['is_active'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['id']); ?>">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($room['name']); ?>" required>
                                <label for="is_active_<?php echo htmlspecialchars($room['id']); ?>">Is Active:</label>
                                <input type="checkbox" id="is_active_<?php echo htmlspecialchars($room['id']); ?>" name="is_active" <?php if ($room['is_active']) echo 'checked'; ?>>
                                <button type="submit" name="update_room">Update</button>
                                <button type="submit" name="delete_room" onclick="return confirm('Are you sure you want to delete this room?');">Delete</button>
                            </form>
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
