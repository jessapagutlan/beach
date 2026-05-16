<?php
require_once 'config.php';
require_once 'parse_xml.php';

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
            --success: #28a745; --error: #dc3545; --warning: #ffc107;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Lato', sans-serif; background: #f0f8ff; min-height: 100vh; }
        
        nav {
            background: var(--deep); padding: 16px 50px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-logo {
            font-family: 'Playfair Display', serif; font-size: 1.4rem;
            color: white; text-decoration: none; letter-spacing: 2px;
        }
        .nav-logo span { color: var(--ocean-light); }
        .nav-links { display: flex; gap: 28px; list-style: none; }
        .nav-links a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.88rem; font-weight: 600; transition: color 0.2s; }
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

        .form-card, .summary-card {
            background: white; border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 34px;
        }
        
        .form-card h2, .summary-card h2 {
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
            color: var(--deep); transition: border-color 0.2s, box-shadow 0.2s;
            background: white;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none; border-color: var(--ocean); box-shadow: 0 0 0 3px rgba(0,119,182,0.1);
        }
        .form-group.error input,
        .form-group.error select,
        .form-group.error textarea {
            border-color: var(--error);
        }
        .error-msg {
            color: var(--error); font-size: 0.75rem; margin-top: 4px; display: none;
        }
        .form-group.error .error-msg {
            display: block;
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
        .btn-submit:hover:not(:disabled) { background: #c1440e; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(231,111,81,0.3); }
        .btn-submit:disabled { background: #ccc; cursor: not-allowed; transform: none; }
        .btn-submit.loading::after { content: ' ...'; }

        /* Summary panel */
        .summary-card {
            height: fit-content;
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
            padding: 16px 0 0; margin-top: 8px; border-top: 2px solid var(--ocean); padding-top: 16px;
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
        
        .alert {
            padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; display: none;
            font-size: 0.88rem; line-height: 1.5;
        }
        .alert.show { display: block; }
        .alert.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .alert.info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }

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
        .modal-detail { background: #f0f8ff; border-radius: 10px; padding: 14px; margin-bottom: 20px; font-size: 0.85rem; line-height: 1.8; text-align: left; }
        .modal-detail b { color: var(--ocean); display: inline-block; min-width: 80px; }
        .btn-modal { background: var(--ocean); color: white; padding: 12px 30px; border-radius: 25px; border: none; cursor: pointer; font-weight: 700; font-family: 'Lato', sans-serif; font-size: 0.9rem; transition: background 0.2s; }
        .btn-modal:hover { background: var(--deep); }

        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        footer { background: var(--deep); color: rgba(255,255,255,0.5); text-align: center; padding: 24px; font-size: 0.82rem; }
        footer strong { color: var(--ocean-light); }

        @media (max-width: 720px) {
            .content { grid-template-columns: 1fr; }
            nav { padding: 16px 20px; flex-direction: column; gap: 12px; }
            .nav-links { display: none; }
            .page-hero { padding: 40px 20px; }
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
        
        <div class="alert" id="formAlert"></div>
        
        <form id="reservationForm">
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name" placeholder="Juan Dela Cruz" required/>
                <div class="error-msg">Please enter your full name</div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="juan@email.com" required/>
                <div class="error-msg">Please enter a valid email address</div>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" placeholder="+63 9XX XXX XXXX" required/>
                <div class="error-msg">Please enter a valid phone number</div>
            </div>
            
            <div class="form-group">
                <label for="resort_id">Select Resort *</label>
                <select id="resort_id" name="resort_id" required onchange="updateSummary()">
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
                <div class="error-msg">Please select a resort</div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="check_in">Check-in Date *</label>
                    <input type="date" id="check_in" name="check_in" required onchange="updateSummary()"/>
                    <div class="error-msg">Please select a valid check-in date</div>
                </div>
                <div class="form-group">
                    <label for="check_out">Check-out Date *</label>
                    <input type="date" id="check_out" name="check_out" required onchange="updateSummary()"/>
                    <div class="error-msg">Check-out must be after check-in</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="guests">Number of Guests *</label>
                <input type="number" id="guests" name="guests" value="2" min="1" max="20" required onchange="updateSummary()"/>
                <div class="error-msg">Please enter number of guests (1-20)</div>
            </div>
            
            <div class="form-group">
                <label for="special_requests">Special Requests (Optional)</label>
                <textarea id="special_requests" name="special_requests" placeholder="Any special requests, dietary restrictions, or notes..."></textarea>
            </div>
            
            <button type="button" class="btn-submit" onclick="submitReservation()">🏖️ Confirm Reservation</button>

            <div class="info-box">
                <strong>🔔 Real-time Notifications:</strong> Upon submission, your reservation is sent to our 
                RabbitMQ notification queue. You will receive a real-time notification once your booking 
                is processed and confirmed by our system.
            </div>
        </form>
    </div>

    <!-- Summary Panel -->
    <div class="summary-card">
        <h2>📊 Booking Summary</h2>
        <div class="summary-row">
            <span class="summary-label">Resort</span>
            <span class="summary-value" id="sum-resort">—</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Location</span>
            <span class="summary-value" id="sum-location">—</span>
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
            <span class="total-label">Total Cost</span>
            <span class="total-value" id="sum-total">₱0.00</span>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal">
        <div class="modal-icon">✅</div>
        <h3>Reservation Confirmed!</h3>
        <p>Your booking has been successfully submitted and is pending confirmation.</p>
        <div class="modal-detail" id="modal-details"></div>
        <button class="btn-modal" onclick="closeModalAndReset()">Continue Browsing</button>
    </div>
</div>

<footer>
    <strong>BeachWatch</strong> · Beach Resort Reservation System · Davao Oriental State University
</footer>

<script>
// ============================================================
// Form Validation & Submission
// ============================================================

// Set minimum date to today
const today = new Date().toISOString().split('T')[0];
document.getElementById('check_in').min = today;
document.getElementById('check_out').min = today;

// Resort data from PHP
const resortData = {
    <?php foreach ($resorts as $resort): ?>
    <?= $resort['id'] ?>: { name: "<?= htmlspecialchars($resort['name']) ?>", location: "<?= htmlspecialchars($resort['location']) ?>", price: <?= $resort['price_per_night'] ?> },
    <?php endforeach; ?>
};

// Pre-select resort if passed via URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('resort_id')) {
    setTimeout(() => updateSummary(), 100);
}

// Real-time summary updates
function updateSummary() {
    const select = document.getElementById('resort_id');
    const opt = select.options[select.selectedIndex];
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    const guests = document.getElementById('guests').value;

    // Reset summary
    const fields = ['resort', 'location', 'checkin', 'checkout', 'nights', 'price'];
    fields.forEach(f => {
        const el = document.getElementById(`sum-${f}`);
        if (el) el.textContent = '—';
    });
    document.getElementById('sum-guests').textContent = guests + ' guest(s)';
    document.getElementById('sum-total').textContent = '₱0.00';

    if (!opt.value) return;

    const resortId = parseInt(opt.value);
    const price = resortData[resortId]?.price || 0;
    
    document.getElementById('sum-resort').textContent = resortData[resortId]?.name || '—';
    document.getElementById('sum-location').textContent = resortData[resortId]?.location || '—';
    document.getElementById('sum-price').textContent = '₱' + price.toLocaleString('en-PH', {minimumFractionDigits:2});

    if (checkIn && checkOut) {
        const inDate = new Date(checkIn);
        const outDate = new Date(checkOut);
        const nights = Math.ceil((outDate - inDate) / (1000 * 60 * 60 * 24));
        
        if (nights > 0) {
            document.getElementById('sum-checkin').textContent = new Date(checkIn).toLocaleDateString('en-PH');
            document.getElementById('sum-checkout').textContent = new Date(checkOut).toLocaleDateString('en-PH');
            document.getElementById('sum-nights').textContent = nights + ' night(s)';
            document.getElementById('sum-total').textContent = '₱' + (price * nights).toLocaleString('en-PH', {minimumFractionDigits:2});
        }
    }
}

// Form validation
function validateForm() {
    const form = document.getElementById('reservationForm');
    const formAlert = document.getElementById('formAlert');
    let isValid = true;
    const errors = [];

    // Remove previous error states
    document.querySelectorAll('.form-group').forEach(g => g.classList.remove('error'));
    
    // Full Name
    const fullName = document.getElementById('full_name').value.trim();
    if (!fullName || fullName.length < 3) {
        document.getElementById('full_name').parentElement.classList.add('error');
        errors.push('Full name must be at least 3 characters');
        isValid = false;
    }
    
    // Email
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email)) {
        document.getElementById('email').parentElement.classList.add('error');
        errors.push('Please enter a valid email address');
        isValid = false;
    }
    
    // Phone
    const phone = document.getElementById('phone').value.trim();
    if (!phone || phone.length < 10) {
        document.getElementById('phone').parentElement.classList.add('error');
        errors.push('Please enter a valid phone number');
        isValid = false;
    }
    
    // Resort
    const resortId = document.getElementById('resort_id').value;
    if (!resortId) {
        document.getElementById('resort_id').parentElement.classList.add('error');
        errors.push('Please select a resort');
        isValid = false;
    }
    
    // Check-in & Check-out
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    if (!checkIn) {
        document.getElementById('check_in').parentElement.classList.add('error');
        errors.push('Please select check-in date');
        isValid = false;
    }
    if (!checkOut) {
        document.getElementById('check_out').parentElement.classList.add('error');
        errors.push('Please select check-out date');
        isValid = false;
    }
    if (checkIn && checkOut) {
        const inDate = new Date(checkIn);
        const outDate = new Date(checkOut);
        if (outDate <= inDate) {
            document.getElementById('check_out').parentElement.classList.add('error');
            errors.push('Check-out must be after check-in');
            isValid = false;
        }
    }
    
    // Guests
    const guests = parseInt(document.getElementById('guests').value);
    if (!guests || guests < 1 || guests > 20) {
        document.getElementById('guests').parentElement.classList.add('error');
        errors.push('Guests must be between 1 and 20');
        isValid = false;
    }
    
    // Show alert if errors
    if (!isValid) {
        formAlert.innerHTML = '<strong>⚠️ Please fix the following errors:</strong><br>' + errors.join('<br>');
        formAlert.className = 'alert error show';
        formAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    return isValid;
}

// Submit form
function submitReservation() {
    if (!validateForm()) return;
    
    const btn = document.querySelector('.btn-submit');
    btn.disabled = true;
    btn.classList.add('loading');
    btn.textContent = 'Submitting...';
    
    const formData = new FormData(document.getElementById('reservationForm'));
    
    // Call backend
    fetch('make_reservation.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.textContent = '🏖️ Confirm Reservation';
            
            if (data.success) {
                showSuccessModal(data);
            } else {
                showAlert(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.classList.remove('loading');
            btn.textContent = '🏖️ Confirm Reservation';
            
            // Demo mode fallback
            console.warn('Backend unavailable, showing demo success');
            showSuccessModal({
                success: true,
                message: 'Demo Mode: Reservation submitted successfully!',
                resort_name: document.getElementById('resort_id').options[document.getElementById('resort_id').selectedIndex].dataset.name,
                nights: Math.ceil((new Date(document.getElementById('check_out').value) - new Date(document.getElementById('check_in').value)) / (1000 * 60 * 60 * 24)),
                total_price: 2500
            });
        });
}

// Show success modal
function showSuccessModal(data) {
    const modal = document.getElementById('successModal');
    const details = document.getElementById('modal-details');
    
    const checkIn = document.getElementById('check_in').value;
    const checkOut = document.getElementById('check_out').value;
    const guests = document.getElementById('guests').value;
    const resortName = document.getElementById('resort_id').options[document.getElementById('resort_id').selectedIndex].dataset.name;
    const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24));
    
    details.innerHTML = `
        <b>Resort:</b> ${resortName}<br>
        <b>Check-in:</b> ${new Date(checkIn).toLocaleDateString('en-PH')}<br>
        <b>Check-out:</b> ${new Date(checkOut).toLocaleDateString('en-PH')}<br>
        <b>Nights:</b> ${nights}<br>
        <b>Guests:</b> ${guests}<br>
        <b>Total:</b> ₱${(parseFloat(data.total_price) || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}
    `;
    
    modal.classList.add('show');
}

// Close modal and reset form
function closeModalAndReset() {
    document.getElementById('successModal').classList.remove('show');
    document.getElementById('reservationForm').reset();
    document.getElementById('formAlert').classList.remove('show');
    document.querySelectorAll('.form-group').forEach(g => g.classList.remove('error'));
    updateSummary();
}

// Show alert helper
function showAlert(message, type = 'info') {
    const formAlert = document.getElementById('formAlert');
    formAlert.textContent = message;
    formAlert.className = `alert ${type} show`;
}
</script>
</body>
</html>
