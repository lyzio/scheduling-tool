<?php
// Definiera root-sökvägen
define('ROOT_PATH', dirname(__DIR__) . '/schema-test/');
define('MAX_ROOMS_PER_TABLE', 3);

// Kontrollera om admin/config.php existerar och inkludera den om den finns
if (file_exists(ROOT_PATH . 'admin/config.php')) {
    require ROOT_PATH . 'admin/config.php';
} else {
    die('Config.php is missing. Please set up the database connection in /admin/login.php.');
}

// Kontrollera om anslutningen till databasen är korrekt
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Hämta alla kategorier från kategoritabellen **Tidigare i koden**
$categories_result = $conn->query("SELECT * FROM categories");
$categories = [];
if ($categories_result) {
    while ($category = $categories_result->fetch_assoc()) {
        $categories[] = $category;
    }
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

// Hämta alla aktiva rum från rooms-tabellen
$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_active = TRUE");
$rooms = [];
while ($room = $rooms_result->fetch_assoc()) {
    $rooms[$room['id']] = $room['name'];
}

// Standardvärden för filter
$selected_date = isset($_GET['event_date']) ? $_GET['event_date'] : (count($available_dates) > 0 ? $available_dates[0] : date('Y-m-d'));
$selected_category = isset($_GET['category']) ? $_GET['category'] : array_column($categories, 'id'); // Ändra $categories_result till $categories
$selected_room = isset($_GET['room']) ? $_GET['room'] : array_keys($rooms);
$show_description = isset($_GET['show_description']) ? $_GET['show_description'] : 'yes';

// Skapa SQL-fråga baserat på filter
$sql = "SELECT schedule.*, categories.name AS category_name, categories.color_hex, rooms.name AS room_name 
        FROM schedule 
        JOIN categories ON schedule.category_id = categories.id
        JOIN rooms ON schedule.room_id = rooms.id
        WHERE event_date = ?";

$param_types = "s"; 
$params = [$selected_date];

// Lägg till ytterligare parametrar till frågan baserat på filter
if (!empty($selected_category) && is_array($selected_category)) {
    $placeholders = implode(',', array_fill(0, count($selected_category), '?'));
    $sql .= " AND category_id IN ($placeholders)";
    $param_types .= str_repeat('i', count($selected_category));
    $params = array_merge($params, $selected_category);
}

if (!empty($selected_room) && is_array($selected_room)) {
    $placeholders = implode(',', array_fill(0, count($selected_room), '?'));
    $sql .= " AND room_id IN ($placeholders)";
    $param_types .= str_repeat('i', count($selected_room));
    $params = array_merge($params, $selected_room);
}

$sql .= " ORDER BY event_time";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

// Dynamisk bindning av parametrar
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Kontrollera om några resultat returnerades
$schedule = []; // Säkerställ att $schedule alltid är definierat
if ($result->num_rows === 0) {
    echo "<p>No events found for the selected date and filters.</p>";
} else {
    while ($row = $result->fetch_assoc()) {
        // Lägg till händelser till schemat per timme
        $event_start_time = new DateTime($row['event_time']);
        $event_end_time = new DateTime($row['end_time']);
        $room_name = $row['room_name'];

        // Loop för varje timme som händelsen täcker
        for ($time = clone $event_start_time; $time < $event_end_time; $time->modify('+1 hour')) {
            $time_key = $time->format('H:i');
            if (!isset($schedule[$time_key])) {
                $schedule[$time_key] = [];
            }
            $schedule[$time_key][$room_name] = $row;
        }
    }
}

$stmt->close(); 
$conn->close(); 

// Konvertera start och sluttid till DateTime-objekt
$start_time_obj = new DateTime($start_time);
$end_time_obj = new DateTime($end_time);

// Skapa rumgrupper baserat på de använda rummen
$used_rooms = [];
foreach ($schedule as $events) {
    foreach ($events as $room_name => $event) {
        if (!in_array($room_name, $used_rooms)) {
            $used_rooms[] = $room_name;
        }
    }
}

// Kontrollera att det finns använda rum innan vi försöker dela upp dem
if (count($used_rooms) > 0) {
    // Sortera rummen alfabetiskt för att säkerställa konsekvent visning
    sort($used_rooms);
    // Skapa grupper av använda rum baserat på maxantalet per tabell
    $room_groups = array_chunk($used_rooms, ceil(count($used_rooms) / ceil(count($used_rooms) / MAX_ROOMS_PER_TABLE)));
} else {
    $room_groups = []; // Inga rum används, så ingen grupp behövs
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Schedule</title>
    <link rel="stylesheet" href="front-style.css">
    <style>
        /* Style för att dölja och visa filtersektionen */
        .filter-section {
            margin-bottom: 20px;
        }
        .filter-button {
            margin: 5px;
        }
        .checkbox-group {
            margin: 10px 0;
        }
        .collapse-button {
            margin-bottom: 10px;
        }
        .hidden {
            display: none;
        }
    </style>
    <script>
        // JavaScript-funktion för att toggla filtersektionen
        function toggleFilters() {
            var filterSection = document.getElementById('filter-section');
            if (filterSection.classList.contains('hidden')) {
                filterSection.classList.remove('hidden');
            } else {
                filterSection.classList.add('hidden');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <!-- Centrera logotypen -->
            <img src="images/logo.png" alt="Logo" class="logo">
        </header>

        <!-- Knapp för att visa/dölja filter -->
        <button class="collapse-button" onclick="toggleFilters()">Toggle Filters</button>

        <!-- Filtreringssektion -->
        <div id="filter-section" class="filter-section">
            <form class="filter-form" method="GET" action="index.php">
                
                <!-- Knappar för val av datum -->
                <div class="date-buttons">
                    <?php foreach ($available_dates as $date): ?>
                        <button type="submit" name="event_date" value="<?php echo htmlspecialchars($date); ?>" class="filter-button <?php echo ($date == $selected_date) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($date); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Kryssrutor för val av kategori -->
                <div class="checkbox-group">
                    <strong>Select Categories:</strong><br>
                    <?php foreach ($categories as $cat): ?>
                        <label>
                            <input type="checkbox" name="category[]" value="<?php echo htmlspecialchars($cat['id']); ?>" <?php echo (in_array($cat['id'], $selected_category)) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>

                <!-- Kryssrutor för val av rum -->
                <div class="checkbox-group">
                    <strong>Select Rooms:</strong><br>
                    <?php foreach ($rooms as $room_id => $room_name): ?>
                        <label>
                            <input type="checkbox" name="room[]" value="<?php echo htmlspecialchars($room_id); ?>" <?php echo (in_array($room_id, $selected_room)) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($room_name); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>

                <!-- Visa beskrivning val -->
                <div class="checkbox-group">
                    <strong>Show Description:</strong><br>
                    <label>
                        <input type="radio" name="show_description" value="yes" <?php echo ($show_description == 'yes') ? 'checked' : ''; ?>> Yes
                    </label>
                    <label>
                        <input type="radio" name="show_description" value="no" <?php echo ($show_description == 'no') ? 'checked' : ''; ?>> No
                    </label>
                </div>

                <input type="submit" value="Filter">
            </form>
        </div>

        <!-- Resten av din schemakod här -->
        <!-- Generera en schematabell för varje rumgrupp -->
        <?php if (!empty($room_groups)): ?>
            <?php foreach ($room_groups as $room_group): ?>
                <?php if (!empty($schedule)): ?>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <?php foreach ($room_group as $room_name): ?>
                                <th><?php echo htmlspecialchars($room_name); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Loop för att skapa schemat mellan det valda tidsintervallet
                        for ($time = clone $start_time_obj; $time <= $end_time_obj; $time->modify('+1 hour')) {
                            $time_str = $time->format('H:i');
                        ?>
                        <tr>
                            <td><?php echo $time->format('H:i'); ?></td>
                            <?php foreach ($room_group as $room_name): ?>
                                <?php if (isset($schedule[$time_str][$room_name])) { 
                                    $event = $schedule[$time_str][$room_name];

                                    // Kontrollera om händelsen är redan visad i föregående rader
                                    if ($time_str == (new DateTime($event['event_time']))->format('H:i')) {
                                        // Beräkna duration i timmar
                                        $event_start_time = new DateTime($event['event_time']);
                                        $event_end_time = new DateTime($event['end_time']);
                                        $duration_hours = $event_end_time->diff($event_start_time)->h;
                                    ?>
                                        <td rowspan="<?php echo $duration_hours; ?>" style="background-color: <?php echo htmlspecialchars($event['color_hex']); ?>">
                                            <?php if ($event['event_link']) { ?>
                                                <a href="<?php echo htmlspecialchars($event['event_link']); ?>" target="_blank">
                                                    <?php echo htmlspecialchars($event['event_name']); ?>
                                                </a>
                                            <?php } else { ?>
                                                <strong><?php echo htmlspecialchars($event['event_name']); ?></strong>
                                            <?php } ?>
                                            <br>
                                            <small><?php echo htmlspecialchars($event_start_time->format('H:i')); ?> - <?php echo htmlspecialchars($event_end_time->format('H:i')); ?></small>
                                            <?php if ($show_description == 'yes' && !empty($event['description'])) { ?>
                                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                                            <?php } ?>
                                        </td>
                                    <?php 
                                    } // Kontroll för första cellen
                                } else { ?>
                                    <td></td>
                                <?php } ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <br> <!-- Liten avstånd mellan tabellerna -->
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No events found for the selected filters.</p>
        <?php endif; ?>

    </div>

    <!-- Sidfot -->
    <footer>
        <p>Made by <a href="http://lyzio.net" target="_blank">Oliver</a> - <a href="admin/login.php">Login</a></p>
    </footer>
</body>
</html>
