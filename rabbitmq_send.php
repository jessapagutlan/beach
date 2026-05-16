<?php
// ============================================================
// BeachWatch — RabbitMQ Producer (Message Sender)
// Sends a notification message when a reservation is created
// Requires: composer require php-amqplib/php-amqplib
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Send a notification message to RabbitMQ queue
 *
 * @param int    $userId      The user who made the reservation
 * @param string $type        Notification type (reservation_confirmed, etc.)
 * @param string $messageText The notification message
 * @param array  $metadata    Optional extra data (resort name, dates, etc.)
 */
function sendNotification($userId, $type, $messageText, $metadata = []) {
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

        // Declare the queue (creates it if it doesn't exist)
        $channel->queue_declare(
            RABBITMQ_QUEUE,
            false,   // passive
            true,    // durable — survives broker restart
            false,   // exclusive
            false    // auto-delete
        );

        // Build the message payload as JSON
        $payload = json_encode([
            'user_id'   => $userId,
            'type'      => $type,
            'message'   => $messageText,
            'metadata'  => $metadata,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Create AMQP message
        $msg = new AMQPMessage($payload, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, // survive broker restart
            'content_type'  => 'application/json'
        ]);

        // Publish to default exchange with routing key = queue name
        $channel->basic_publish($msg, '', RABBITMQ_QUEUE);

        $channel->close();
        $connection->close();

        error_log("[BeachWatch] Notification sent to RabbitMQ: $messageText");
        return true;

    } catch (Exception $e) {
        error_log("[BeachWatch] RabbitMQ producer error: " . $e->getMessage());
        return false;
    }
}

// ============================================================
// Convenience wrappers for common notification types
// ============================================================

function notifyReservationConfirmed($userId, $resortName, $checkIn, $checkOut) {
    $msg = "Your reservation at $resortName has been CONFIRMED! Check-in: $checkIn, Check-out: $checkOut.";
    return sendNotification($userId, 'reservation_confirmed', $msg, [
        'resort_name' => $resortName,
        'check_in'    => $checkIn,
        'check_out'   => $checkOut
    ]);
}

function notifyReservationPending($userId, $resortName) {
    $msg = "Your reservation at $resortName is PENDING confirmation. We will notify you shortly.";
    return sendNotification($userId, 'reservation_pending', $msg, [
        'resort_name' => $resortName
    ]);
}

function notifyReservationCancelled($userId, $resortName) {
    $msg = "Your reservation at $resortName has been CANCELLED.";
    return sendNotification($userId, 'reservation_cancelled', $msg, [
        'resort_name' => $resortName
    ]);
}
