-- Skapa tabell för kategorier
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    color_hex VARCHAR(7) NOT NULL
);

-- Skapa tabell för rum
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Skapa tabell för händelser
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    end_time TIME NOT NULL,
    category_id INT,
    room_id INT,
    event_link VARCHAR(255),
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

-- Skapa tabell för inställningar
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(255) NOT NULL,
    setting_value TEXT NOT NULL
);

-- Infoga standardvärden för kategorier
INSERT INTO categories (name, color_hex) VALUES
('Rollspel', '#ff5e57'),
('Föreläsning', '#ef5777'),
('Scenprogram', '#ffc048');

-- Infoga standardvärden för rum
INSERT INTO rooms (name, is_active) VALUES
('Första rummet', TRUE),
('Andra rummet', TRUE),
('Tredje rummet', TRUE);

-- Infoga standardvärden för inställningar
INSERT INTO settings (setting_name, setting_value) VALUES
('available_dates', '2024-08-24,2024-08-25,2024-08-26'),
('time_range_start', '07:00'),
('time_range_end', '24:00');
