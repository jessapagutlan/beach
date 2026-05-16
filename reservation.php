<?php
require_once 'php/config.php';
require_once 'php/parse_xml.php';

$resorts = parseResortsXML();
$preselectedId = intval($_GET['resort_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>BeachWatch — Make a Reservation</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Lato:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --ocean: #0077b6; --deep: #03045e; --coral: #e76f51;
            --ocean-light: #00b4d8; --gray: #6c757d; --sand: #f4e1c0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Lato', sans-serif; background: #f0f8ff; min-height: 100vh; }
        nav {
            background: var(--deep); padding: 16px 50px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .nav-logo {
            font-family: 'Playfair Display', serif; font-size: 1.4rem;
            color: white; text-decoration: none; letter-spacing: 2px;
        }
        .nav-logo span { color: var(--ocean-light); }
        .nav-links { display: flex; gap: 28px; list-style: none; }
        .nav-links a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.88rem; font-weight: 600; }
        .nav-links a:hover, .nav-links .active { color: var(--ocean-light); }

        .page-hero {
            background: linear-gradient(135deg, var(--ocean), var(--deep));
            padding: 60px 50px 40px; color: white;
        }
        .page-hero h1 { font-family: 'Playfair Display', serif; font-size: 2.4rem; margin-bottom: 8px; }
        .page-hero p { color: rgba(255,255,255,0.75); font-size: 0.95rem; }

        .content {
            max-width: 900px; margin: 0 auto; padding: 50px 30px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 36px;
        }

        .form-card {
            background: white; border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 34px;
        }
        .form-card h2 {
            font-family: 'Playfair Display', serif; font-size: 1.5rem;
            color: var(--deep); margin-bottom: 24px; padding-bottom: 16px;
            border-bottom: 2px solid #e8f4fd;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 0.82rem; font-weight: 700;
            color: var(--deep); margin-bottom: 6px; letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 12px 14px; border: 2px solid #e0e0e0;
            border-radius: 10px; font-family: 'Lato', sans-serif; font-size: 0.9rem;
            color: var(--deep); transition: border-color 0.2s;
            background: white;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none; border-color: var(--ocean);
        }
        .form-group textarea { height: 90px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .btn-submit {
            width: 100%; padding: 14px; background: var(--coral);
            color: white; border: none; border-radius: 12px;
            font-family: 'Lato', sans-serif; font-size: 1rem;
            font-weight: 700; cursor: pointer; transition: all 0.25s;
            letter-spacing: 0.5px;
        }
        .btn-submit:hover { background: #c1440e; transform: translateY(-1px); }
        .btn-submit:disabled { background: #ccc; cursor: not-allowed; transform: none; }

        /* Summary panel */
        .summary-card {
            background: white; border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 30px;
            height: fit-content;
        }
        .summary-card h2 {
            font-family: 'Playfair Display', serif; font-size: 1.3rem;
            color: var(--deep); margin-bottom: 22px; padding-bottom: 14px;
            border-bottom: 2px solid #e8f4fd;
        }
        .summary-row {
            display: flex; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid #f0f0f0;
            font-size: 0.88rem;
        }
        .summary-row:last-of-type { border-bottom: none; }
        .summary-label { color: var(--gray); }
        .summary-value { font-weight: 600; color: var(--deep); }
        .total-row {
            display: flex; justify-content: space-between;
            padding: 16px 0 0; margin-top: 8px;
        }
        .total-label { font-weight: 700; font-size: 1rem; color: var(--deep); }
        .total-value {
            font-family: 'Playfair Display', serif; font-size: 1.5rem;
            color: var(--coral); font-weight: 700;
        }

        .info-box {
            background: #e8f4fd; border-left: 4px solid var(--ocean);
            padding: 12px 16px; border-radius: 0 8px 8px 0;
            font-size: 0.82rem; color: #333; margin-top: 20px; line-height: 1.6;
        }
        .info-box strong { color: var(--ocean); }

        /* Success modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 9999;
            align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: white; border-radius: 20px; padding: 40px;
            max-width: 420px; width: 90%; text-align: center;
            animation: popIn 0.3s ease;
        }
        .modal-icon { font-size: 3.5rem; margin-bottom: 16px; }
        .modal h3 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: var(--deep); margin-bottom: 10px; }
        .modal p { color: var(--gray); font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px; }
        .modal-detail { background: #f0f8ff; border-radius: 10px; padding: 14px; margin-bottom: 20px; font-size: 0.85rem; line-height: 1.8; }
        .btn-modal { background: var(--ocean); color: white; padding: 12px 30px; border-radius: 25px; border: none; cursor: pointer; font-weight: 700; font-family: 'Lato', sans-serif; font-size: 0.9rem; }

        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        footer { background: var(--deep); color: rgba(255,255,255,0.5); text-align: center; padding: 24px; font-size: 0.82rem; }
        footer strong { color: var(--ocean-light); }

        @media (max-width: 720px) {
            .content { grid-template-columns: 1fr; }
            nav { padding: 16px 20px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

<nav>
    <a href="index.html" class="nav-logo">Beach<span>Watch</span></a>
    <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="resorts.php">Resorts</a></li>
        <li><a href="reservation.php" class="active">Reserve</a></li>
        <li><a href="notifications.php">Notifications</a></li>
    </ul>
</nav>

<div class="page-hero">
    <h1>Make a Reservation</h1>
    <p>Book your perfect beach getaway in Davao Oriental</p>
</div>

<div class="content">
    <!-- Reservation Form -->
    <div class="form-card">
        <h2>📋 Booking Details</h2>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" id="full_name" placeholder="Juan Dela Cruz" required/>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" id="email" placeholder="juan@email.com" required/>
        </div>
        <div class="form-group">
            <label>Select Resort</label>
            <select id="resort_id" onchange="updateSummary()">
                <option value="">— Choose a Resort —</option>
                <?php foreach ($resorts as $resort): ?>
                <option value="<?= $resort['id'] ?>"
                    data-price="<?= $resort['price_per_night'] ?>"
                    data-name="<?= htmlspecialchars($resort['name']) ?>"
                    <?= ($preselectedId == $resort['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($resort['name']) ?> — ₱<?= number_format($resort['price_per_night'], 2) ?>/night
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Check-in Date</label>
                <input type="date" id="check_in" onchange="updateSummary()" required/>
            </div>
            <div class="form-group">
                <label>Check-out Date</label>
                <input type="date" id="check_out" onchange="updateSummary()" required/>
            </div>
        </div>
        <div class="form-group">
            <label>Number of Guests</label>
            <input type="number" id="guests" value="2" min="1" max="20" onchange="updateSummary()"/>
        </div>
        <div class="form-group">
            <label>Special Requests (Optional)</label>
            <textarea id="special_requests" placeholder="Any special requests or notes..."></textarea>
        </div>
        <button class="btn-submit" onclick="submitReservation()">🏖️ Confirm Reservation</button>

        <div class="info-box">
            <strong>🔔 RabbitMQ Messaging:</strong> Upon submission, your reservation is sent to the
            RabbitMQ notification queue. You will receive a real-time notification once your booking
            is processed by our system.
        </div>
    </div>

    <!-- Summary Panel -->
    <div class="summary-card">
        <h2>📊 Booking Summary</h2>
        <div class="summary-row">
            <span class="summary-label">Resort</span>
            <span class="summary-value" id="sum-resort">—</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Check-in</span>
            <span class="summary-value" id="sum-checkin">—</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Check-out</span>
            <span class="summary-value" id="sum-checkout">—</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Nights</span>
            <span class="summary-value" id="sum-nights">—</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Guests</span>
            <span class="summary-value" id="sum-guests">—</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Price/night</span>
            <span class="summary-value" id="sum-price">—</span>
        </div>
        <div class="total-row">
            <span class="total-label">Total</span>
            <span class="total-value" id="sum-total">₱0.00</span>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal">
        <div class="modal-icon">🎉</div>
        <h3>Reservation Submitted!</h3>
        <p>Your booking is now <strong>pending confirmation</strong>. A notification has been sent via RabbitMQ.</p>
        <div class="modal-detail" id="modal-details"></div>
        <button class="btn-modal" onclick="document.getElementById('successModal').classList.remove('show')">
            Close
        </button>
    </div>
</div>

<footer>
    <strong>BeachWatch</strong> · ITP 121 Final Project · Davao Oriental State University
</footer>

<script>
// Set minimum date to today
const today = new Date().toISOString().split('T')[0];
document.getElementById('check_in').min = today;
document.getElementById('check_out').min = today;

// Pre-select resort if passed via URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('resort_id')) updateSummary();

function updateSummary() {
    const select = document.getElementById('resort_id');
    const opt = select.options[select.selectedIndex];
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    const guests = document.getElementById('guests').value;

    if (opt.value) {
        const price = parseFloat(opt.dataset.price || 0);
        document.getElementById('sum-resort').textContent = opt.dataset.name || '—';
        document.getElementById('sum-price').textContent = '₱' + price.toLocaleString('en-PH', {minimumFractionDigits:2});
        document.getElementById('sum-guests').textContent = guests + ' guest(s)';

        if (checkIn && checkOut) {
            const inDate = new Date(checkIn);
            const outDate = new Date(checkOut);
            const nights = Math.ceil((outDate - inDate) / (1000 * 60 * 60 * 24));
            if (nights > 0) {
                document.getElementById('sum-checkin').textContent = checkIn;
                document.getElementById('sum-checkout').textContent = checkOut;
                document.getElementById('sum-nights').textContent = nights + ' night(s)';
                document.getElementById('sum-total').textContent = '₱' + (price * nights).toLocaleString('en-PH', {minimumFractionDigits:2});
                return;
            }
        }
    }
    document.getElementById('sum-resort').textContent = opt.value ? (opt.dataset.name || '—') : '—';
    document.getElementById('sum-checkin').textContent = checkIn || '—';
    document.getElementById('sum-checkout').textContent = checkOut || '—';
    document.getElementById('sum-nights').textContent = '—';
    document.getElementById('sum-total').textContent = '₱0.00';
}

function submitReservation() {
    const resortId = document.getElementById('resort_id').value;
    const checkIn  = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    const guests   = document.getElementById('guests').value;
    const name     = document.getElementById('full_name').value;
    const email    = document.getElementById('email').value;

    if (!name || !email || !resortId || !checkIn || !checkOut) {
        alert('Please fill in all required fields.');
        return;
    }
    if (new Date(checkOut) <= new Date(checkIn)) {
        alert('Check-out must be after check-in.');
        return;
    }

    const btn = document.querySelector('.btn-submit');
    btn.disabled = true; btn.textContent = 'Submitting...';

    const form = new FormData();
    form.append('resort_id', resortId);
    form.append('check_in', checkIn);
    form.append('check_out', checkOut);
    form.append('guests', guests);
    form.append('special_requests', document.getElementById('special_requests').value);
    form.append('user_id', 2); // Demo: logged-in user ID

    fetch('php/make_reservation.php', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false; btn.textContent = '🏖️ Confirm Reservation';
            if (data.success) {
                const select = document.getElementById('resort_id');
                const resortName = select.options[select.selectedIndex].dataset.name;
                document.getElementById('modal-details').innerHTML =
                    `<b>Resort:</b> ${resortName}<br>
                     <b>Check-in:</b> ${checkIn}<br>
                     <b>Check-out:</b> ${checkOut}<br>
                     <b>Guests:</b> ${guests}<br>
                     <b>Total:</b> ₱${parseFloat(data.total_price).toLocaleString('en-PH', {minimumFractionDigits:2})}`;
                document.getElementById('successModal').classList.add('show');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(() => {
            btn.disabled = false; btn.textContent = '🏖️ Confirm Reservation';
            // Demo mode — show success even without backend
            document.getElementById('modal-details').innerHTML =
                `<b>Demo Mode:</b> Reservation form submitted!<br>
                 <b>Note:</b> Connect XAMPP + MySQL to enable full functionality.`;
            document.getElementById('successModal').classList.add('show');
        });
}
</script>
</body>
</html>
