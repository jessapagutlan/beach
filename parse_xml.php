<?php
// ============================================================
// BeachWatch — XML DOM Parser
// Reads resorts.xml and returns resort data as PHP array
// ============================================================

require_once 'config.php';

/**
 * Parse resorts.xml using DOM (tree-based XML parsing)
 * Returns an array of resort data
 */
function parseResortsXML() {
    $xmlFile = XML_PATH . 'resorts.xml';

    if (!file_exists($xmlFile)) {
        return ['error' => 'resorts.xml not found'];
    }

    // Load XML into DOM Document
    $dom = new DOMDocument();
    $dom->load($xmlFile);

    $resorts = [];
    $resortNodes = $dom->getElementsByTagName('resort');

    foreach ($resortNodes as $resortNode) {
        $resort = [];

        // Get resort ID attribute
        $resort['id'] = $resortNode->getAttribute('id');

        // Get text content of child elements
        $resort['name']            = getNodeValue($resortNode, 'name');
        $resort['location']        = getNodeValue($resortNode, 'location');
        $resort['description']     = getNodeValue($resortNode, 'description');
        $resort['price_per_night'] = getNodeValue($resortNode, 'price_per_night');
        $resort['capacity']        = getNodeValue($resortNode, 'capacity');
        $resort['status']          = getNodeValue($resortNode, 'status');

        // Contact info (nested elements)
        $contactNode = $resortNode->getElementsByTagName('contact')->item(0);
        if ($contactNode) {
            $resort['contact_email'] = getNodeValue($contactNode, 'email');
            $resort['contact_phone'] = getNodeValue($contactNode, 'phone');
        }

        // Amenities (multiple child elements)
        $amenityNodes = $resortNode->getElementsByTagName('amenity');
        $amenities = [];
        foreach ($amenityNodes as $amenityNode) {
            $amenities[] = $amenityNode->nodeValue;
        }
        $resort['amenities'] = $amenities;

        $resorts[] = $resort;
    }

    return $resorts;
}

/**
 * Parse reservations.xml using DOM
 */
function parseReservationsXML() {
    $xmlFile = XML_PATH . 'reservations.xml';

    if (!file_exists($xmlFile)) {
        return ['error' => 'reservations.xml not found'];
    }

    $dom = new DOMDocument();
    $dom->load($xmlFile);

    $reservations = [];
    $resNodes = $dom->getElementsByTagName('reservation');

    foreach ($resNodes as $resNode) {
        $reservation = [];
        $reservation['id']         = $resNode->getAttribute('id');
        $reservation['check_in']   = getNodeValue($resNode, 'check_in');
        $reservation['check_out']  = getNodeValue($resNode, 'check_out');
        $reservation['guests']     = getNodeValue($resNode, 'guests');
        $reservation['total_price']= getNodeValue($resNode, 'total_price');
        $reservation['status']     = getNodeValue($resNode, 'status');
        $reservation['special_requests'] = getNodeValue($resNode, 'special_requests');
        $reservation['created_at'] = getNodeValue($resNode, 'created_at');

        // Nested user info
        $userNode = $resNode->getElementsByTagName('user')->item(0);
        if ($userNode) {
            $reservation['user_name']  = getNodeValue($userNode, 'name');
            $reservation['user_email'] = getNodeValue($userNode, 'email');
        }

        // Nested resort info
        $resortNode = $resNode->getElementsByTagName('resort')->item(0);
        if ($resortNode) {
            $reservation['resort_name'] = getNodeValue($resortNode, 'name');
        }

        $reservations[] = $reservation;
    }

    return $reservations;
}

/**
 * Helper: Get text value of a child element
 */
function getNodeValue($parentNode, $tagName) {
    $node = $parentNode->getElementsByTagName($tagName)->item(0);
    return $node ? trim($node->nodeValue) : '';
}

/**
 * Save a new reservation to reservations.xml
 */
function appendReservationToXML($reservationData) {
    $xmlFile = XML_PATH . 'reservations.xml';

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    if (file_exists($xmlFile)) {
        $dom->load($xmlFile);
        $root = $dom->getElementsByTagName('reservations')->item(0);
    } else {
        $root = $dom->createElement('reservations');
        $dom->appendChild($root);
    }

    // Get next ID
    $existing = $dom->getElementsByTagName('reservation');
    $newId = $existing->length + 1;

    // Build new reservation node
    $resNode = $dom->createElement('reservation');
    $resNode->setAttribute('id', $newId);

    $fields = ['check_in', 'check_out', 'guests', 'total_price', 'status', 'special_requests', 'created_at'];
    foreach ($fields as $field) {
        $el = $dom->createElement($field, htmlspecialchars($reservationData[$field] ?? ''));
        $resNode->appendChild($el);
    }

    $root->appendChild($resNode);
    $dom->save($xmlFile);

    return $newId;
}

// ============================================================
// If called directly, output parsed XML as JSON (for testing)
// ============================================================
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json');
    echo json_encode([
        'resorts' => parseResortsXML(),
        'reservations' => parseReservationsXML()
    ], JSON_PRETTY_PRINT);
}
