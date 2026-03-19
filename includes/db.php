<?php
/**
 * Ujamaa Hostel - Database Configuration
 * MySQL database connection and setup
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ujamaa_hostel');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Set your MySQL password here

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

/**
 * Get database connection
 */
function getDBConnection() {
    try {
        $socket = '/Applications/MAMP/tmp/mysql/mysql.sock';
        $dsn = file_exists($socket) ? "mysql:unix_socket=$socket;dbname=" . DB_NAME . ";charset=utf8mb4" : "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}

/**
 * Initialize database tables
 */
function initializeDatabase() {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        return false;
    }
    
    try {
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
        
        // Insert default room data
        $rooms = [
            ['standard', 'Standard Room', 45, 2, 'Comfortable standard room with shared bathroom'],
            ['deluxe', 'Deluxe Room', 55, 2, 'Spacious room with private bathroom and extra amenities'],
            ['family', 'Family Room', 100, 6, 'Large family room with multiple beds and seating area'],
            ['dorm', 'Dorm Bed', 20, 1, 'Bunk bed in shared dormitory with lockers'],
            ['twin', 'Twin Room', 45, 2, 'Room with two single beds and private bathroom'],
            ['camping', 'Camping', 15, 2, 'Bring your own tent and enjoy our garden']
        ];
        
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO rooms (room_type, room_name, price_per_night, capacity, description, amenities)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($rooms as $room) {
            $amenities = json_encode(['wifi', 'bed_linen']);
            $stmt->execute([$room[0], $room[1], $room[2], $room[3], $room[4], $amenities]);
        }
        
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
        
        // Insert default testimonials
        $testimonials = [
            ['Sarah Williams', 'UK', 5, 'The mamas are incredible and the food is the best in Arusha! Feel like home.'],
            ['Marcus Chen', 'Germany', 5, 'The vibe here is unmatched. I came for two days and ended up staying for two weeks.'],
            ['Elena Rossi', 'Italy', 4, 'Very safe and clean. Perfect base for my Kilimanjaro trek.'],
            ['David Smith', 'USA', 5, 'Great volunteer community. Njiro is a quiet and safe area.']
        ];
        
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO testimonials (guest_name, country, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($testimonials as $t) {
            $stmt->execute([$t[0], $t[1], $t[2], $t[3]]);
        }

        // Create admin users table
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
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Database Initialization Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique booking reference
 */
function generateBookingReference() {
    return 'UJM-' . strtoupper(bin2hex(random_bytes(4)));
}

/**
 * Send confirmation email
 */
function sendConfirmationEmail($bookingData, $bookingReference) {
    $to = $bookingData['email'];
    $subject = "Ujamaa Hostel - Booking Confirmation (" . $bookingReference . ")";
    
    $message = "
    <html>
    <head>
        <title>Booking Confirmation</title>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: #2E7D32; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h1 style='margin: 0;'>Ujamaa Hostel</h1>
                <p style='margin: 5px 0 0;'>Arusha, Tanzania</p>
            </div>
            <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;'>
                <h2 style='color: #2E7D32;'>Booking Confirmed!</h2>
                <p>Dear {$bookingData['first_name']},</p>
                <p>Thank you for choosing Ujamaa Hostel! Your booking has been confirmed.</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0;'>Booking Details</h3>
                    <p><strong>Reference:</strong> {$bookingReference}</p>
                    <p><strong>Room Type:</strong> " . ucfirst($bookingData['room_type']) . "</p>
                    <p><strong>Check-in:</strong> {$bookingData['checkin']}</p>
                    <p><strong>Check-out:</strong> {$bookingData['checkout']}</p>
                    <p><strong>Guests:</strong> {$bookingData['guests']}</p>
                    <p><strong>Total Price:</strong> \${$bookingData['total_price']}</p>
                </div>
                
                <p>If you have any questions or need to modify your booking, please contact us.</p>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;'>
                    <p style='color: #666; font-size: 14px;'>
                        <strong>Contact Us:</strong><br>
                        Phone: +255 753 960 570<br>
                        Email: booking.ujamaa.hostel@gmail.com
                    </p>
                </div>
            </div>
            <div style='text-align: center; padding: 20px; color: #666; font-size: 12px;'>
                <p>&copy; " . date('Y') . " Ujamaa Hostel. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-Type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Ujamaa Hostel <noreply@ujamahostel.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Get room prices from database
 */
function getRoomPrices() {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        return null;
    }
    
    try {
        $stmt = $pdo->query("SELECT room_type, room_name, price_per_night, capacity FROM rooms WHERE is_available = TRUE");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching rooms: " . $e->getMessage());
        return null;
    }
}

/**
 * Check room availability
 */
function checkRoomAvailability($roomType, $checkin, $checkout) {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE room_type = ? 
            AND status IN ('pending', 'confirmed')
            AND NOT (checkout_date <= ? OR checkin_date >= ?)
        ");
        
        $stmt->execute([$roomType, $checkin, $checkout]);
        $result = $stmt->fetch();
        
        return $result['count'] == 0;
    } catch (PDOException $e) {
        error_log("Error checking availability: " . $e->getMessage());
        return false;
    }
}

// Initialize database on include
initializeDatabase();