<?php
// ============================================================
// BeachWatch — RabbitMQ Consumer (Message Receiver)
// Run this as a background process:
//   php rabbitmq_receive.php
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

echo "[BeachWatch] Starting RabbitMQ consumer...\n";
echo "[BeachWatch] Waiting for messages on queue: " . RABBITMQ_QUEUE . "\n";
echo "[BeachWatch] Press Ctrl+C to stop.\n\n";

try {
    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection(
        RABBITMQ_HOST,
        RABBITMQ_PORT,
        RABBITMQ_USER,
        RABBITMQ_PASS,
        RABBITMQ_VHOST
    );

    $channel = $connection->channel();

    // Declare queue (must match producer)
    $channel->queue_declare(
        RABBITMQ_QUEUE,
        false,
        true,  // durable
        false,
        false
    );

    // Fair dispatch — only 1 unacknowledged message per worker at a time
    $channel->basic_qos(null, 1, null);

    // Callback function: runs when a message arrives
    $callback = function ($msg) {
        $data = json_decode($msg->body, true);

        echo "[" . date('Y-m-d H:i:s') . "] New notification received:\n";
        echo "  User ID : " . ($data['user_id'] ?? 'N/A') . "\n";
        echo "  Type    : " . ($data['type'] ?? 'N/A') . "\n";
        echo "  Message : " . ($data['message'] ?? 'N/A') . "\n";
        echo "  Time    : " . ($data['timestamp'] ?? 'N/A') . "\n\n";

        // Save to database
        try {
            $conn = getDBConnection();
            $userId  = intval($data['user_id'] ?? 0);
            $message = $conn->real_escape_string($data['message'] ?? '');
            $type    = $conn->real_escape_string($data['type'] ?? 'general');

            if ($userId > 0) {
                $conn->query(
                    "INSERT INTO notifications (user_id, message, type) 
                     VALUES ($userId, '$message', '$type')"
                );
                echo "  [DB] Notification saved to database.\n\n";
            }
            $conn->close();
        } catch (Exception $dbErr) {
            echo "  [DB ERROR] " . $dbErr->getMessage() . "\n\n";
        }

        // Acknowledge message so RabbitMQ removes it from queue
        $msg->ack();
    };

    // Start consuming messages
    $channel->basic_consume(
        RABBITMQ_QUEUE,
        '',    // consumer tag
        false, // no-local
        false, // no-ack (we manually ack)
        false, // exclusive
        false, // no-wait
        $callback
    );

    // Keep consuming until interrupted
    while ($channel->is_open()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();

} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
