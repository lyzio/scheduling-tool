<?php
// Kontrollera om config.php redan finns
if (file_exists('config.php')) {
    die('Installation is already complete. To reinstall, delete config.php and run this script again.');
}

// Hantera formulärinmatning
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_password = $_POST['db_password'];
    $db_name = $_POST['db_name'];

    // Försök att ansluta till databasen
    $conn = new mysqli($db_host, $db_user, $db_password);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Skapa databasen om den inte redan finns
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $db_name") === TRUE) {
        echo "Database created successfully<br>";
    } else {
        die("Error creating database: " . $conn->error);
    }

    // Välj den skapade databasen
    $conn->select_db($db_name);

    // Skapa nödvändiga tabeller
    $sql = "
    CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE
    );

    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        color_hex VARCHAR(7) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS schedule (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_name VARCHAR(255) NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        end_time TIME NOT NULL,
        category_id INT NOT NULL,
        room_id INT NOT NULL,
        event_link VARCHAR(255),
        description TEXT,
        FOREIGN KEY (category_id) REFERENCES categories(id),
        FOREIGN KEY (room_id) REFERENCES rooms(id)
    );
    ";

    if ($conn->multi_query($sql) === TRUE) {
        echo "Tables created successfully<br>";
    } else {
        die("Error creating tables: " . $conn->error);
    }

    // Stäng anslutningen
    $conn->close();

    // Skapa config.php-filen
    $config_content = "<?php\n";
    $config_content .= "define('DB_HOST', '$db_host');\n";
    $config_content .= "define('DB_USER', '$db_user');\n";
    $config_content .= "define('DB_PASSWORD', '$db_password');\n";
    $config_content .= "define('DB_NAME', '$db_name');\n";

    if (file_put_contents('config.php', $config_content)) {
        echo "config.php created successfully<br>";
        echo "<a href='login.php'>Go to login page</a>";
    } else {
        die("Error creating config.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .install-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .install-container input[type="text"],
        .install-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .install-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h2>Install</h2>
        <form method="POST" action="">
            <input type="text" name="db_host" placeholder="Database Host" required>
            <input type="text" name="db_user" placeholder="Database User" required>
            <input type="password" name="db_password" placeholder="Database Password" required>
            <input type="text" name="db_name" placeholder="Database Name" required>
            <button type="submit">Install</button>
        </form>
    </div>
</body>
</html>
