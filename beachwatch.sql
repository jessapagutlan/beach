-- ============================================================
-- BeachWatch: Beach Resort Reservation System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS beachwatch;
USE beachwatch;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'tourist') DEFAULT 'tourist',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- RESORTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS resorts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(150) NOT NULL,
    description TEXT,
    amenities TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    capacity INT NOT NULL,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- RESERVATIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    resort_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resort_id) REFERENCES resorts(id) ON DELETE CASCADE
);

-- ============================================================
-- NOTIFICATIONS TABLE (for RabbitMQ messages)
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('reservation_confirmed', 'reservation_cancelled', 'new_inquiry', 'general') DEFAULT 'general',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- MESSAGES / INQUIRIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    resort_id INT,
    subject VARCHAR(200),
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resort_id) REFERENCES resorts(id) ON DELETE SET NULL
);

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin user (password: admin123 — bcrypt hash)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@beachwatch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BeachWatch Admin', 'admin');

-- Sample tourist
INSERT INTO users (username, email, password, full_name, phone, role) VALUES
('juandelacruz', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Dela Cruz', '09171234567', 'tourist');

-- Sample resorts (Davao Oriental themed)
INSERT INTO resorts (name, location, description, amenities, price_per_night, capacity, contact_email, contact_phone) VALUES
('Dahican Surf Resort', 'Dahican Beach, Mati City, Davao Oriental', 'World-class surfing destination with pristine white sand beach and crystal-clear waters perfect for surfing and relaxation.', 'Swimming Pool, Surfboard Rental, Restaurant, Free WiFi, Parking, Beach Access', 2500.00, 30, 'dahican@resort.com', '09181234567'),
('Cape San Agustin Beach Resort', 'Cape San Agustin, Governor Generoso, Davao Oriental', 'Breathtaking cliff views and untouched natural beauty. Perfect for adventurers and nature lovers.', 'Cottages, Snorkeling Gear, Bonfire Area, Camping Ground, Guided Tours', 1800.00, 20, 'capesan@resort.com', '09182345678'),
('Pujada Bay Eco Resort', 'Pujada Bay, Mati City, Davao Oriental', 'Eco-friendly resort nestled in the biodiversity hotspot of Pujada Bay. Ideal for marine enthusiasts.', 'Diving Equipment, Kayak Rental, Mangrove Tours, Restaurant, Solar Power', 3200.00, 25, 'pujada@resort.com', '09183456789'),
('Aliwagwag Paradise Resort', 'Aliwagwag, Cateel, Davao Oriental', 'Located near the majestic Aliwagwag Falls. Experience the magic of cascading waterfalls and lush rainforest.', 'Waterfall Tours, Swimming Area, Cottage Rental, BBQ Area, Nature Trails', 1500.00, 40, 'aliwagwag@resort.com', '09184567890'),
('Tarragona Beach Club', 'Tarragona, Davao Oriental', 'Quiet and secluded beach resort ideal for family getaways and group events. Stunning sunrise views.', 'Function Hall, Beach Volleyball, Restaurant, Karaoke, Water Sports', 2200.00, 50, 'tarragona@resort.com', '09185678901');
