# 🌊 BeachWatch — Beach Resort Reservation System
**ITP 121 – Integrative Programming & Technologies 1 | Final Project**
Davao Oriental State University | BSIT

---

## 📁 Project Structure

```
beachwatch/
├── index.html               ← Landing page
├── resorts.php              ← Resort listings (XSLT + DOM parsed view)
├── reservation.php          ← Reservation form
├── notifications.php        ← RabbitMQ notification inbox
├── composer.json            ← PHP dependencies
│
├── xml/
│   ├── resorts.xml          ← XML data: all resorts
│   └── reservations.xml     ← XML log: all reservations
│
├── xslt/
│   └── resorts.xsl          ← XSLT stylesheet (XML → HTML)
│
├── php/
│   ├── config.php           ← DB + RabbitMQ config
│   ├── parse_xml.php        ← DOM XML Parser
│   ├── transform_xslt.php   ← XSLT Transformation
│   ├── make_reservation.php ← Reservation handler
│   ├── rabbitmq_send.php    ← RabbitMQ Producer
│   └── rabbitmq_receive.php ← RabbitMQ Consumer
│
├── scripts/
│   └── start_services.sh    ← Bash automation script
│
└── db/
    └── beachwatch.sql       ← MySQL database schema + sample data
```

---

## ⚙️ Setup Instructions

### Step 1 — Copy to XAMPP

Copy the entire `beachwatch/` folder to:
```
C:\xampp\htdocs\beachwatch\
```

### Step 2 — Start XAMPP

Open **XAMPP Control Panel** and start:
- ✅ Apache
- ✅ MySQL

### Step 3 — Create the Database

1. Open **MySQL Workbench** (or phpMyAdmin at `http://localhost/phpmyadmin`)
2. Run the file: `db/beachwatch.sql`
3. This creates the `beachwatch` database with all tables and sample data.

### Step 4 — Install PHP Dependencies (RabbitMQ library)

Open **Git Bash** inside the `beachwatch/` folder:
```bash
composer install
```
This installs `php-amqplib` for RabbitMQ support.

### Step 5 — Start RabbitMQ

Make sure RabbitMQ is running (it should auto-start after installation).
To verify, open: `http://localhost:15672` (user: `guest`, pass: `guest`)

### Step 6 — Run the Consumer (Background)

In **Git Bash**:
```bash
php php/rabbitmq_receive.php
```
Keep this terminal open. It listens for notifications.

Or use the Bash script:
```bash
bash scripts/start_services.sh start
```

### Step 7 — Open the Website

Visit: **http://localhost/beachwatch/**

---

## 🔧 Technologies Used

| Requirement       | Implementation                                      |
|-------------------|-----------------------------------------------------|
| Messaging System  | RabbitMQ + php-amqplib (Producer + Consumer)        |
| XML Data          | `resorts.xml`, `reservations.xml`                  |
| XML Parsing       | DOM Parsing via PHP `DOMDocument`                   |
| XSLT              | `resorts.xsl` → HTML via PHP `XSLTProcessor`        |
| Scripting         | `start_services.sh` (Bash automation script)        |
| Backend           | PHP 7.4+                                            |
| Database          | MySQL via XAMPP                                     |
| Frontend          | HTML5, CSS3, JavaScript                             |

---

## 👥 Suggested Role Assignments

| Member | Role          | Files                                              |
|--------|---------------|----------------------------------------------------|
| 1      | Frontend      | `index.html`, `reservation.php`, `notifications.php` |
| 2      | Backend       | `make_reservation.php`, `config.php`, `db/`        |
| 3      | XML/XSLT      | `resorts.xml`, `reservations.xml`, `resorts.xsl`   |
| 4      | Messaging     | `rabbitmq_send.php`, `rabbitmq_receive.php`        |
| 5      | Docs+Scripts  | `start_services.sh`, documentation, screenshots    |

---

## 📌 Notes

- Default MySQL credentials: `root` / *(blank)* — update `php/config.php` if different
- RabbitMQ default credentials: `guest` / `guest`
- PHP needs the `xsl` extension enabled — check `php.ini` and uncomment `;extension=xsl`
- All features are functional and integrated end-to-end
