<?php
// Definiera root-sökvägen
define('ROOT_PATH', dirname(__DIR__) . '/schema-test/');

// Kontrollera om admin/config.php existerar och inkludera den om den finns
if (file_exists(ROOT_PATH . 'admin/config.php')) {
    require ROOT_PATH . 'admin/config.php';
} else {
    die('Config.php is missing. Please set up the database connection in /admin/login.php.');
}

// Hämta de tillgängliga datumen från databasen
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

// Standardvärden för filter
$selected_date = isset($_GET['event_date']) ? $_GET['event_date'] : (count($available_dates) > 0 ? $available_dates[0] : date('Y-m-d'));
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
$selected_room = isset($_GET['room']) ? $_GET['room'] : '';  // Nytt filter för rum

// Hämta alla aktiva rum från rooms-tabellen
$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_active = TRUE");
$rooms = [];
while ($room = $rooms_result->fetch_assoc()) {
    $rooms[$room['id']] = $room['name'];
}

// Hämta alla kategorier från kategoritabellen
$categories_result = $conn->query("SELECT * FROM categories");
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}

// Skapa SQL-fråga baserat på filter
$sql = "SELECT schedule.*, categories.name AS category_name, categories.color_hex, rooms.name AS room_name 
        FROM schedule 
        JOIN categories ON schedule.category_id = categories.id
        JOIN rooms ON schedule.room_id = rooms.id
        WHERE event_date = ?";

$param_types = "s"; // parameter typ för $selected_date
$params = [$selected_date];

// Lägg till ytterligare parametrar till frågan baserat på filter
if ($selected_category) {
    $sql .= " AND category_id = ?";
    $param_types .= "i"; // parameter typ för category_id
    $params[] = $selected_category;
}

if ($selected_room) {
    $sql .= " AND room_id = ?";
    $param_types .= "i"; // parameter typ för room_id
    $params[] = $selected_room;
}

$sql .= " ORDER BY event_time";

$stmt = $conn->prepare($sql);

// Bind parametrarna dynamiskt
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Skapa ett array för att organisera schemat efter tid och rum
$schedule = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedule[$row['event_time']][$row['room_name']] = $row;
    }
}

$stmt->close(); // Stäng uttalandet
$conn->close(); // Stäng anslutningen

// Konvertera start och sluttid till DateTime-objekt
$start_time_obj = new DateTime($start_time);
$end_time_obj = new DateTime($end_time);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Schedule</title>
    <link rel="stylesheet" href="front-style.css">
</head>
<body>
    <header>
        <!-- Visa uppladdad logotyp -->
        <img src="images/logo.png" alt="Logo" class="logo">
    </header>

    <!-- Filterformulär för kategori, dag och rum -->
    <form class="filter-form" method="GET" action="index.php">
        <label for="event_date">Select Date:</label>
        <select id="event_date" name="event_date">
            <?php foreach ($available_dates as $date): ?>
                <option value="<?php echo htmlspecialchars($date); ?>" <?php if ($date == $selected_date) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($date); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <label for="category">Select Category:</label>
        <select id="category" name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php if ($cat['id'] == $selected_category) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="room">Select Room:</label>
        <select id="room" name="room">
            <option value="">All Rooms</option>
            <?php foreach ($rooms as $room_id => $room_name): ?>
                <option value="<?php echo htmlspecialchars($room_id); ?>" <?php if ($room_id == $selected_room) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($room_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <input type="submit" value="Filter">
    </form>
    
    <!-- Schematabell -->
    <table class="schedule-table">
        <thead>
            <tr>
                <th>Time</th>
                <?php foreach ($rooms as $room_name): ?>
                    <th><?php echo htmlspecialchars($room_name); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop för att skapa schemat mellan det valda tidsintervallet
            for ($time = clone $start_time_obj; $time <= $end_time_obj; $time->modify('+1 hour')):
                $time_str = $time->format('H:i:s');
            ?>
            <tr>
                <td><?php echo $time->format('H:i'); ?></td>
                <?php foreach ($rooms as $room_name): ?>
                    <?php if (isset($schedule[$time_str][$room_name])): 
                        $event = $schedule[$time_str][$room_name];
                        $event_start_time = new DateTime($event['event_time']);
                        $event_end_time = new DateTime($event['end_time']);
                        $duration_hours = $event_end_time->diff($event_start_time)->h;
                        ?>
                        <td rowspan="<?php echo $duration_hours; ?>" style="background-color: <?php echo htmlspecialchars($event['color_hex']); ?>">
                            <strong><?php echo htmlspecialchars($event['event_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($event['event_time']); ?> - <?php echo htmlspecialchars($event['end_time']); ?></small>
                        </td>
                    <?php else: ?>
                        <td></td>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Sidfot -->
    <footer>
        <p>Made by <a href="http://lyzio.net" target="_blank">Oliver</a></p>
    </footer>
</body>
</html>
