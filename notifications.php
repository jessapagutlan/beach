<?php
require_once 'php/config.php';
// In full system: query from DB. Below is demo data.
$notifications = [
    ['id'=>1, 'type'=>'reservation_confirmed', 'message'=>'Your reservation at Dahican Surf Resort has been CONFIRMED! Check-in: 2025-06-10, Check-out: 2025-06-13.', 'is_read'=>0, 'created_at'=>'2025-05-16 08:35:00'],
    ['id'=>2, 'type'=>'reservation_pending', 'message'=>'Your reservation at Pujada Bay Eco Resort is PENDING confirmation. We will notify you shortly.', 'is_read'=>0, 'created_at'=>'2025-05-16 10:05:00'],
    ['id'=>3, 'type'=>'general', 'message'=>'Welcome to BeachWatch! Start exploring our amazing beach resorts in Davao Oriental.', 'is_read'=>1, 'created_at'=>'2025-05-15 09:00:00'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BeachWatch — Notifications</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Lato:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <style>
        :root { --ocean:#0077b6;--deep:#03045e;--coral:#e76f51;--ocean-light:#00b4d8;--gray:#6c757d; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Lato',sans-serif; background:#f0f8ff; min-height:100vh; }
        nav { background:var(--deep);padding:16px 50px;display:flex;justify-content:space-between;align-items:center; }
        .nav-logo { font-family:'Playfair Display',serif;font-size:1.4rem;color:white;text-decoration:none;letter-spacing:2px; }
        .nav-logo span { color:var(--ocean-light); }
        .nav-links { display:flex;gap:28px;list-style:none; }
        .nav-links a { color:rgba(255,255,255,0.8);text-decoration:none;font-size:0.88rem;font-weight:600; }
        .nav-links a:hover, .nav-links .active { color:var(--ocean-light); }

        .page-hero { background:linear-gradient(135deg,var(--ocean),var(--deep));padding:60px 50px 40px;color:white; }
        .page-hero h1 { font-family:'Playfair Display',serif;font-size:2.4rem;margin-bottom:8px; }
        .page-hero p { color:rgba(255,255,255,0.75);font-size:0.95rem; }

        .content { max-width:760px;margin:0 auto;padding:50px 30px; }

        .info-box { background:#e8f4fd;border-left:4px solid var(--ocean);padding:14px 18px;border-radius:0 8px 8px 0;font-size:0.85rem;color:#333;margin-bottom:28px;line-height:1.6; }
        .info-box strong { color:var(--ocean); }

        .notif-card {
            background:white;border-radius:14px;padding:22px 24px;margin-bottom:16px;
            box-shadow:0 2px 14px rgba(0,0,0,0.06);
            border-left:4px solid #ddd;
            display:flex;gap:16px;align-items:flex-start;
            transition:box-shadow 0.2s;
        }
        .notif-card:hover { box-shadow:0 6px 24px rgba(0,119,182,0.12); }
        .notif-card.unread { border-left-color:var(--ocean); background:#f8fbff; }
        .notif-card.confirmed { border-left-color:#2ecc71; }
        .notif-card.cancelled { border-left-color:var(--coral); }

        .notif-icon { font-size:1.8rem; flex-shrink:0; margin-top:2px; }
        .notif-body { flex:1; }
        .notif-type { font-size:0.72rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--ocean);margin-bottom:6px; }
        .notif-msg { font-size:0.9rem;color:#333;line-height:1.6;margin-bottom:8px; }
        .notif-time { font-size:0.76rem;color:var(--gray); }
        .unread-dot { width:8px;height:8px;background:var(--ocean);border-radius:50%;flex-shrink:0;margin-top:8px; }

        .empty-state { text-align:center;padding:60px 20px;color:var(--gray); }
        .empty-state div { font-size:3rem;margin-bottom:14px; }

        footer { background:var(--deep);color:rgba(255,255,255,0.5);text-align:center;padding:24px;font-size:0.82rem; }
        footer strong { color:var(--ocean-light); }
    </style>
</head>
<body>

<nav>
    <a href="index.html" class="nav-logo">Beach<span>Watch</span></a>
    <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="resorts.php">Resorts</a></li>
        <li><a href="reservation.php">Reserve</a></li>
        <li><a href="notifications.php" class="active">Notifications</a></li>
    </ul>
</nav>

<div class="page-hero">
    <h1>🔔 Notifications</h1>
    <p>Real-time reservation updates delivered via RabbitMQ message broker</p>
</div>

<div class="content">
    <div class="info-box">
        <strong>🐇 RabbitMQ Messaging System:</strong> Notifications are produced and consumed via
        RabbitMQ. When a reservation is submitted, <code>rabbitmq_send.php</code> publishes a message
        to the <code>beachwatch_notifications</code> queue. The consumer script
        <code>rabbitmq_receive.php</code> reads and saves each notification to the database.
    </div>

    <?php if (empty($notifications)): ?>
    <div class="empty-state">
        <div>🔕</div>
        <p>No notifications yet. Make a reservation to receive updates!</p>
    </div>
    <?php else: ?>
        <?php foreach ($notifications as $n):
            $icon = match($n['type']) {
                'reservation_confirmed' => '✅',
                'reservation_pending'   => '⏳',
                'reservation_cancelled' => '❌',
                default                 => '📢'
            };
            $typeLabel = str_replace('_', ' ', strtoupper($n['type']));
            $cardClass = $n['is_read'] ? '' : 'unread';
            if ($n['type'] === 'reservation_confirmed') $cardClass .= ' confirmed';
            if ($n['type'] === 'reservation_cancelled') $cardClass .= ' cancelled';
        ?>
        <div class="notif-card <?= $cardClass ?>">
            <div class="notif-icon"><?= $icon ?></div>
            <div class="notif-body">
                <div class="notif-type"><?= $typeLabel ?></div>
                <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                <div class="notif-time">📅 <?= $n['created_at'] ?></div>
            </div>
            <?php if (!$n['is_read']): ?>
            <div class="unread-dot"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<footer>
    <strong>BeachWatch</strong> · ITP 121 Final Project · Davao Oriental State University
</footer>
</body>
</html>
