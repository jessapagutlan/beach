<?php
// ============================================================
// BeachWatch — Reservation Handler
// Handles form submission for booking a resort
// Supports both demo mode (without DB) and production mode (with DB)
// ============================================================

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Sanitize and collect input
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$resortId = intval($_POST['resort_id'] ?? 0);
$checkIn = trim($_POST['check_in'] ?? '');
$checkOut = trim($_POST['check_out'] ?? '');
$guests = intval($_POST['guests'] ?? 1);
$specialRequests = trim($_POST['special_requests'] ?? '');

// Basic validation
$errors = [];

if (empty($fullName) || strlen($fullName) < 3) {
    $errors[] = 'Full name must be at least 3 characters';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address';
}

if (empty($phone) || strlen($phone) < 10) {
    $errors[] = 'Invalid phone number';
}

if (!$resortId) {
    $errors[] = 'Resort selection is required';
}

if (empty($checkIn) || empty($checkOut)) {
    $errors[] = 'Check-in and check-out dates are required';
}

if ($guests < 1 || $guests > 20) {
    $errors[] = 'Guests must be between 1 and 20';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => implode('; ', $errors)
    ]);
    exit;
}

// Validate dates
try {
    $inDate = new DateTime($checkIn);
    $outDate = new DateTime($checkOut);
    $today = new DateTime('today');
    
    if ($inDate < $today) {
        throw new Exception('Check-in date must be in the future');
    }
    
    if ($outDate <= $inDate) {
        throw new Exception('Check-out must be after check-in');
    }
    
    $nights = $inDate->diff($outDate)->days;
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid dates: ' . $e->getMessage()
    ]);
    exit;
}

// Resort pricing data (hardcoded for demo)
$resorts = [
    1 => ['name' => 'Dahican Surf Resort', 'price' => 2500],
    2 => ['name' => 'Cape San Agustin Resort', 'price' => 1800],
    3 => ['name' => 'Pujada Bay Eco Resort', 'price' => 3200],
    4 => ['name' => 'Aliwagwag Paradise Resort', 'price' => 1500],
    5 => ['name' => 'Tarragona Beach Club', 'price' => 2200],
];

if (!isset($resorts[$resortId])) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Resort not found'
    ]);
    exit;
}

$resort = $resorts[$resortId];
$totalPrice = $nights * $resort['price'];

// Try to save to database if configured
$reservationId = null;
$dbSuccess = false;

try {
    // Load config if available
    if (file_exists('config.php')) {
        require_once 'config.php';
        
        // Try to connect to database
        if (function_exists('getDBConnection')) {
            $conn = @getDBConnection();
            
            if ($conn && !$conn->connect_error) {
                // Prepare sanitized input
                $fullNameEsc = $conn->real_escape_string($fullName);
                $emailEsc = $conn->real_escape_string($email);
                $phoneEsc = $conn->real_escape_string($phone);
                $requestsEsc = $conn->real_escape_string($specialRequests);
                
                $sql = "INSERT INTO reservations (user_id, resort_id, full_name, email, phone, check_in, check_out, guests, total_price, special_requests, status, created_at)
                        VALUES (0, $resortId, '$fullNameEsc', '$emailEsc', '$phoneEsc', '$checkIn', '$checkOut', $guests, $totalPrice, '$requestsEsc', 'pending', NOW())";
                
                if ($conn->query($sql)) {
                    $reservationId = $conn->insert_id;
                    $dbSuccess = true;
                    
                    // Try to log to XML if function exists
                    if (function_exists('appendReservationToXML')) {
                        @appendReservationToXML([
                            'reservation_id' => $reservationId,
                            'full_name' => $fullName,
                            'email' => $email,
                            'resort_id' => $resortId,
                            'check_in' => $checkIn,
                            'check_out' => $checkOut,
                            'guests' => $guests,
                            'total_price' => $totalPrice,
                            'status' => 'pending',
                            'special_requests' => $specialRequests,
                            'created_at' => date('Y-m-d\TH:i:s')
                        ]);
                    }
                    
                    // Try to send RabbitMQ notification if function exists
                    if (function_exists('notifyReservationPending')) {
                        @notifyReservationPending(0, $resort['name'], $fullName, $email);
                    }
                }
                
                $conn->close();
            }
        }
    }
} catch (Exception $e) {
    // Database unavailable, continue with demo mode
    error_log('Database error: ' . $e->getMessage());
}

// Generate reservation ID for demo mode if not already set
if (!$reservationId) {
    $reservationId = 'RES-' . strtoupper(substr(md5(microtime()), 0, 8));
}

// Success response
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Reservation submitted successfully! ' . ($dbSuccess ? 'You will receive a confirmation shortly.' : '(Demo Mode)'),
    'reservation_id' => $reservationId,
    'resort_name' => $resort['name'],
    'guest_name' => $fullName,
    'guest_email' => $email,
    'check_in' => $checkIn,
    'check_out' => $checkOut,
    'nights' => $nights,
    'guests' => $guests,
    'total_price' => $totalPrice,
    'db_saved' => $dbSuccess
]);
