<?php
/**
 * Ujamaa Hostel - Book Room API
 * Handles booking requests from the frontend
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Include database configuration
require_once 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$requiredFields = ['room_type', 'first_name', 'last_name', 'email', 'phone', 'checkin', 'checkout'];
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
$roomType = htmlspecialchars(strip_tags($input['room_type']));
$firstName = htmlspecialchars(strip_tags($input['first_name']));
$lastName = htmlspecialchars(strip_tags($input['last_name']));
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(strip_tags($input['phone']));
$checkin = $input['checkin'];
$checkout = $input['checkout'];
$guests = isset($input['guests']) ? (int)$input['guests'] : 1;
$specialRequests = isset($input['special_requests']) ? htmlspecialchars(strip_tags($input['special_requests'])) : '';

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]);
    exit;
}

// Validate dates
$checkinDate = new DateTime($checkin);
$checkoutDate = new DateTime($checkout);
$today = new DateTime();

if ($checkinDate < $today) {
    echo json_encode([
        'success' => false,
        'message' => 'Check-in date cannot be in the past'
    ]);
    exit;
}

if ($checkoutDate <= $checkinDate) {
    echo json_encode([
        'success' => false,
        'message' => 'Check-out date must be after check-in date'
    ]);
    exit;
}

// Calculate nights and price
$nights = $checkoutDate->diff($checkinDate)->days;
$roomPrices = [
    'standard' => 25,
    'deluxe' => 40,
    'family' => 80,
    'dorm' => 12,
    'twin' => 35,
    'camping' => 8
];

if (!isset($roomPrices[$roomType])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid room type'
    ]);
    exit;
}

$pricePerNight = $roomPrices[$roomType];
$totalPrice = $pricePerNight * $nights;

// Get database connection
$pdo = getDBConnection();

if (!$pdo) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.'
    ]);
    exit;
}

try {
    // Check if room is available
    if (!checkRoomAvailability($roomType, $checkin, $checkout)) {
        echo json_encode([
            'success' => false,
            'message' => 'Sorry, this room is not available for your selected dates. Please choose different dates or another room type.'
        ]);
        exit;
    }
    
    // Generate booking reference
    $bookingReference = generateBookingReference();
    
    // Insert booking into database
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            booking_reference, room_type, first_name, last_name, email, phone,
            checkin_date, checkout_date, guests, total_price, special_requests, status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending'
        )
    ");
    
    $stmt->execute([
        $bookingReference,
        $roomType,
        $firstName,
        $lastName,
        $email,
        $phone,
        $checkin,
        $checkout,
        $guests,
        $totalPrice,
        $specialRequests
    ]);
    
    $bookingId = $pdo->lastInsertId();
    
    // Send confirmation email (optional - may fail silently)
    $bookingData = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'room_type' => $roomType,
        'checkin' => $checkin,
        'checkout' => $checkout,
        'guests' => $guests,
        'total_price' => $totalPrice
    ];
    
    @sendConfirmationEmail($bookingData, $bookingReference);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Booking confirmed successfully!',
        'booking_reference' => $bookingReference,
        'booking_id' => $bookingId,
        'details' => [
            'room_type' => ucfirst($roomType),
            'checkin' => $checkin,
            'checkout' => $checkout,
            'nights' => $nights,
            'guests' => $guests,
            'total_price' => $totalPrice
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Booking Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your booking. Please try again.'
    ]);
}