<?php
/**
 * Ujamaa Hostel - Contact API
 * Handles contact form submissions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

require_once 'db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = ['first_name', 'last_name', 'email', 'subject', 'message'];
$errors = [];

foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        $errors[] = "Field '$field' is required";
    }
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
    ]);
    exit;
}

// Sanitize input
$firstName = htmlspecialchars(strip_tags($input['first_name']));
$lastName = htmlspecialchars(strip_tags($input['last_name']));
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$phone = isset($input['phone']) ? htmlspecialchars(strip_tags($input['phone'])) : '';
$subject = htmlspecialchars(strip_tags($input['subject']));
$message = htmlspecialchars(strip_tags($input['message']));

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]);
    exit;
}

$pdo = getDBConnection();

if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO contacts (first_name, last_name, email, phone, subject, message, status)
        VALUES (?, ?, ?, ?, ?, ?, 'unread')
    ");
    
    $stmt->execute([$firstName, $lastName, $email, $phone, $subject, $message]);
    
    // Send auto-reply email (optional)
    $to = $email;
    $emailSubject = "Ujamaa Hostel - We received your message";
    $emailMessage = "
    <html>
    <body style='font-family: Arial, sans-serif; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto;'>
            <div style='background: #059669; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;'>
                <h1 style='margin: 0;'>Ujamaa Hostel</h1>
            </div>
            <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;'>
                <h2>Thank you for contacting us!</h2>
                <p>Dear $firstName,</p>
                <p>We have received your message and will get back to you within 24 hours.</p>
                <hr>
                <h4>Your Message:</h4>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong></p>
                <p>$message</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Ujamaa Hostel <noreply@ujamahostel.com>\r\n";
    
    @mail($to, $emailSubject, $emailMessage, $headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully! We\'ll get back to you soon.'
    ]);
    
} catch (PDOException $e) {
    error_log("Contact Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}