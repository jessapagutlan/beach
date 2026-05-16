<?php
// ============================================================
// BeachWatch — Reservation Handler
// Handles form submission for booking a resort
// ============================================================

session_start();
require_once 'config.php';
require_once 'rabbitmq_send.php';
require_once 'parse_xml.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Collect and sanitize input
$conn      = getDBConnection();
$userId    = intval($_POST['user_id'] ?? ($_SESSION['user_id'] ?? 0));
$resortId  = intval($_POST['resort_id'] ?? 0);
$checkIn   = $conn->real_escape_string($_POST['check_in'] ?? '');
$checkOut  = $conn->real_escape_string($_POST['check_out'] ?? '');
$guests    = intval($_POST['guests'] ?? 1);
$requests  = $conn->real_escape_string($_POST['special_requests'] ?? '');

// Basic validation
if (!$userId || !$resortId || !$checkIn || !$checkOut || $guests < 1) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// Validate dates
$in  = new DateTime($checkIn);
$out = new DateTime($checkOut);
if ($out <= $in) {
    echo json_encode(['success' => false, 'message' => 'Check-out must be after check-in.']);
    exit;
}

// Get resort price from DB
$resortResult = $conn->query("SELECT name, price_per_night FROM resorts WHERE id = $resortId AND status = 'active'");
if ($resortResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resort not found.']);
    exit;
}
$resort     = $resortResult->fetch_assoc();
$nights     = $in->diff($out)->days;
$totalPrice = $nights * $resort['price_per_night'];

// Insert reservation into DB
$sql = "INSERT INTO reservations (user_id, resort_id, check_in, check_out, guests, total_price, special_requests, status)
        VALUES ($userId, $resortId, '$checkIn', '$checkOut', $guests, $totalPrice, '$requests', 'pending')";

if ($conn->query($sql)) {
    $reservationId = $conn->insert_id;

    // Also log to XML file
    appendReservationToXML([
        'check_in'         => $checkIn,
        'check_out'        => $checkOut,
        'guests'           => $guests,
        'total_price'      => $totalPrice,
        'status'           => 'pending',
        'special_requests' => $requests,
        'created_at'       => date('Y-m-d\TH:i:s')
    ]);

    // Send RabbitMQ notification
    notifyReservationPending($userId, $resort['name']);

    echo json_encode([
        'success'        => true,
        'message'        => 'Reservation submitted successfully! You will receive a confirmation shortly.',
        'reservation_id' => $reservationId,
        'resort_name'    => $resort['name'],
        'nights'         => $nights,
        'total_price'    => $totalPrice
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save reservation: ' . $conn->error]);
}

$conn->close();
