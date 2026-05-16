<?php
// ============================================================
// BeachWatch — Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change if you have a MySQL password
define('DB_PASS', 'Pagutlan123!456');            // Your MySQL password (blank by default in XAMPP)
define('DB_NAME', 'beachwatch');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset("utf8");
    return $conn;
}

// RabbitMQ Configuration
define('RABBITMQ_HOST', 'localhost');
define('RABBITMQ_PORT', 5672);
define('RABBITMQ_USER', 'guest');
define('RABBITMQ_PASS', 'guest');
define('RABBITMQ_VHOST', '/');
define('RABBITMQ_QUEUE', 'beachwatch_notifications');

// App Config
define('APP_NAME', 'BeachWatch');
define('BASE_URL', 'http://localhost/beachwatch');
define('XML_PATH', __DIR__ . '/../xml/');
define('XSLT_PATH', __DIR__ . '/../xslt/');
