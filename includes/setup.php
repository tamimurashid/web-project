<?php
/**
 * Ujamaa Hostel - Database Setup Script
 * Run this file to set up the MySQL database
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Ujamaa Hostel - Database Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css' rel='stylesheet'>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; padding: 40px; display: flex; justify-content: center; min-height: 100vh; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; font-weight: 700; color: #0f172a; }
        .setup-card { max-width: 700px; width: 100%; margin: 0 auto; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
        .success { color: #059669; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .text-primary { color: #059669 !important; }
        .btn-primary { background: linear-gradient(135deg, #059669, #047857); border: none; font-family: 'Outfit', sans-serif; font-weight: 600; padding: 0.75rem 1.5rem; transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(5, 150, 105, 0.3); }
        .btn-outline-primary { color: #059669; border: 2px solid #059669; font-family: 'Outfit', sans-serif; font-weight: 600; padding: 0.65rem 1.4rem; transition: all 0.3s; }
        .btn-outline-primary:hover { background: #059669; color: white; transform: translateY(-2px); }
        .list-group-item.success { border-left: 4px solid #059669; }
    </style>
</head>
<body>
<div class='container d-flex align-items-start pt-5'>
    <div class='setup-card p-5'>
        <div class='text-center mb-5'>
            <div class='d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3' style='width: 80px; height: 80px;'>
                <i class='bi bi-database-fill-gear fs-1 text-primary'></i>
            </div>
            <h2 class='mb-2'>MySQL Initialization</h2>
            <p class='text-muted'>Ujamaa Hostel Database Setup Wizard</p>
        </div>";

$errors = [];
$success = [];

// Database configuration - Update these values
$dbHost = 'localhost';
$dbName = 'ujamaa_hostel';
$dbUser = 'root';
$dbPass = 'root'; // Set your MySQL password

try {
    // Connect to MySQL
    $socket = '/Applications/MAMP/tmp/mysql/mysql.sock';
    $dsn = file_exists($socket) ? "mysql:unix_socket=$socket" : "mysql:host=$dbHost";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $success[] = "Connected to MySQL successfully";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbName");
    $pdo->exec("USE $dbName");
    $success[] = "Database '$dbName' created/verified";
    
    // Create bookings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_reference VARCHAR(20) UNIQUE NOT NULL,
            room_type VARCHAR(50) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(30) NOT NULL,
            checkin_date DATE NOT NULL,
            checkout_date DATE NOT NULL,
            guests INT DEFAULT 1,
            total_price DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
            special_requests TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_booking_ref (booking_reference),
            INDEX idx_email (email),
            INDEX idx_status (status),
            INDEX idx_checkin (checkin_date),
            INDEX idx_checkout (checkout_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $success[] = "Table 'bookings' created";
    
    // Create contacts table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(30),
            subject VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $success[] = "Table 'contacts' created";
    
    // Create rooms table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            room_type VARCHAR(50) UNIQUE NOT NULL,
            room_name VARCHAR(100) NOT NULL,
            price_per_night DECIMAL(10,2) NOT NULL,
            capacity INT DEFAULT 2,
            description TEXT,
            amenities JSON,
            is_available BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $success[] = "Table 'rooms' created";
    
    // Create testimonials table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            guest_name VARCHAR(150) NOT NULL,
            country VARCHAR(100),
            rating INT DEFAULT 5,
            comment TEXT NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $success[] = "Table 'testimonials' created";
    
    // Create admins table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $success[] = "Table 'admins' created";
    
    // Insert default rooms
    $rooms = [
        ['standard', 'Standard Room', 25, 2, 'Comfortable standard room with shared bathroom'],
        ['deluxe', 'Deluxe Room', 40, 2, 'Spacious room with private bathroom and extra amenities'],
        ['family', 'Family Room', 80, 6, 'Large family room with multiple beds and seating area'],
        ['dorm', 'Dorm Bed', 12, 1, 'Bunk bed in shared dormitory with lockers'],
        ['twin', 'Twin Room', 35, 2, 'Room with two single beds and private bathroom'],
        ['camping', 'Camping', 8, 2, 'Bring your own tent and enjoy our garden']
    ];
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO rooms (room_type, room_name, price_per_night, capacity, description, amenities)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($rooms as $room) {
        $amenities = json_encode(['wifi', 'bed_linen']);
        $stmt->execute([$room[0], $room[1], $room[2], $room[3], $room[4], $amenities]);
    }
    $success[] = "Default room data inserted";
    
    // Insert sample testimonial
    $pdo->exec("
        INSERT IGNORE INTO testimonials (guest_name, country, rating, comment)
        VALUES ('Michael K.', 'United Kingdom', 5, 'Perfect base for our Kilimanjaro trek! The staff arranged everything - transfers, equipment, even packed lunches. Can not recommend enough!')
    ");
    $success[] = "Sample testimonial inserted";
    
    // Insert default admin user if none exists
    $adminUser = 'admin';
    $adminEmail = 'admin@ujamaahostel.com';
    $adminPass = 'ujamaa2024'; // Using same original hardcoded password for easy access initially
    $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
    $stmt->execute([$adminUser]);
    if ($stmt->fetchColumn() == 0) {
        $insertAdmin = $pdo->prepare("
            INSERT INTO admins (username, email, password_hash, full_name, role) 
            VALUES (?, ?, ?, 'System Administrator', 'admin')
        ");
        $insertAdmin->execute([$adminUser, $adminEmail, $hashedPass]);
        $success[] = "Default admin user created (Username: <strong>$adminUser</strong> / Password: <strong>$adminPass</strong>)";
    } else {
        $success[] = "Default admin user already exists";
    }
    
    echo "<div class='alert alert-success border-0 shadow-sm rounded-4 p-4 mb-4' style='background-color: #ecfdf5;'>
            <h5 class='alert-heading d-flex align-items-center' style='color: #065f46;'><i class='bi bi-check-circle-fill fs-3 me-2'></i> Setup Complete!</h5>
            <p class='mb-0' style='color: #065f46;'>All database tables have been created successfully.</p>
          </div>";
    
    echo "<h5 class='mt-4'>Steps Completed:</h5>
          <ul class='list-group'>";
    foreach ($success as $msg) {
        echo "<li class='list-group-item success'><i class='bi bi-check me-2'></i> $msg</li>";
    }
    echo "</ul>";
    
    echo "<div class='alert alert-info border-0 shadow-sm rounded-4 p-4 mt-4 bg-light'>
        <h5 class='text-dark mb-3'><i class='bi bi-info-circle text-primary me-2'></i> Next Steps:</h5>
        <ol class='mb-0 text-muted' style='line-height: 1.8;'>
            <li>Update database credentials in <code class='bg-white px-2 py-1 rounded'>includes/db.php</code> if needed</li>
            <li>Access the booking website at <code class='bg-white px-2 py-1 rounded'>index.html</code></li>
            <li>Access the admin panel at <code class='bg-white px-2 py-1 rounded'>admin/admin.php</code></li>
            <li>Default admin login: <strong>admin</strong> / <strong>ujamaa2024</strong></li>
        </ol>
    </div>";
    
    echo "<div class='text-center mt-5 d-flex gap-3 justify-content-center'>
        <a href='../index.html' class='btn btn-outline-primary rounded-pill px-4'>
            <i class='bi bi-house me-2'></i> Go to Website
        </a>
        <a href='../admin/admin.php' class='btn btn-primary rounded-pill px-4'>
            <i class='bi bi-speedometer2 me-2'></i> Admin Panel
        </a>
    </div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>
        <h5 class='alert-heading'><i class='bi bi-exclamation-triangle-fill'></i> Setup Failed</h5>
        <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
        <hr>
        <p class='mb-0'>Please make sure:</p>
        <ul>
            <li>MySQL is running</li>
            <li>Database credentials are correct in this file</li>
            <li>You have permission to create databases</li>
        </ul>
    </div>";
}

echo "
    </div>
</div>
</body>
</html>";