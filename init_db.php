<?php
require_once 'includes/config.php';

try {
    // Create database directory if it doesn't exist
    $dbDir = dirname(DB_PATH);
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    // Connect to SQLite database
    $db = new PDO("sqlite:" . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS vehicles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            brand TEXT NOT NULL,
            model TEXT NOT NULL,
            year INTEGER NOT NULL,
            category TEXT NOT NULL,
            daily_rate REAL NOT NULL,
            description TEXT,
            image_url TEXT,
            features TEXT,
            status TEXT DEFAULT 'available' CHECK(status IN ('available', 'maintenance', 'reserved')),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS reservations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reservation_number TEXT NOT NULL UNIQUE,
            vehicle_id INTEGER NOT NULL,
            client_name TEXT NOT NULL,
            client_email TEXT NOT NULL,
            client_phone TEXT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'confirmed', 'rejected', 'completed', 'cancelled')),
            total_price REAL NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS vehicle_availability (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            vehicle_id INTEGER NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status TEXT DEFAULT 'available' CHECK(status IN ('available', 'unavailable')),
            reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
        )
    ");

    // Insert admin user
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("
        INSERT OR IGNORE INTO users (email, password, first_name, last_name, is_admin) 
        VALUES ('admin@ddrental.com', '$adminPassword', 'Admin', 'User', 1)
    ");

    // Insert sample vehicles
    $db->exec("
        INSERT OR IGNORE INTO vehicles (name, brand, model, year, category, daily_rate, description, status) VALUES
        ('BMW X5 M Sport', 'BMW', 'X5', 2023, 'SUV', 250.00, 'SUV luxueux avec finition M Sport', 'available'),
        ('Mercedes C300 AMG Line', 'Mercedes', 'Classe C', 2023, 'Berline', 200.00, 'Berline élégante avec pack AMG', 'available'),
        ('Audi RS Q8', 'Audi', 'Q8', 2023, 'SUV', 350.00, 'SUV sportif haut de gamme', 'available')
    ");

    echo "Database initialized successfully!\n";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage() . "\n");
}
