# CAVA LMS

![Home page mockup](file:///C:/Users/Shivam/.gemini/antigravity-ide/brain/5c12796d-f005-42d3-bb80-84634db1515d/home_page_mockup_1781945540314.png)

> **Empower Your Learning Journey**

## ✨ Overview
CAVA LMS is a modern, premium‑looking Learning Management System built with **PHP 8**, **MySQL**, and **Bootstrap 5**. It provides a clean UI, robust admin panel, and essential e‑learning features such as:
- Course, Webinar, Event, and Support management
- Razorpay payment integration
- Role‑based authentication (admin & user)
- Powerful filtering and searching on public pages
- Seed scripts that generate 10+ mock records for each module
- Responsive design with glass‑morphism, gradient accents and subtle micro‑animations

## 🚀 Live Demo
You can try the demo on a local XAMPP installation or deploy it to any standard PHP host (e.g., Hostinger).

## 📦 Prerequisites
- PHP 8.0+ (with PDO extension)
- MySQL 5.7+ (or MariaDB)
- Composer (for vendor dependencies)
- XAMPP / WAMP or any LAMP stack

## 🛠️ Installation
```bash
# Clone the repository (if you haven't already)
git clone https://github.com/iam-shivam/cava-lms.git
cd cava-lms

# Install PHP dependencies via Composer
composer install

# Create the database and import the schema
mysql -u root -p < schema.sql

# (Optional) Seed the database with mock data
php seed_large.php
```

## ⚙️ Configuration
1. Copy `config/config.example.php` to `config/config.php`.
2. Edit the following constants in `config/config.php`:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - `SITE_URL` – base URL of the application (e.g., `http://localhost/lms`)
   - Razorpay keys – `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET`
3. Ensure the `uploads/` directory is writable by the web server.

## 📂 Project Structure
```
├─ admin/            # Admin panel (CRUD for all modules)
├─ assets/           # CSS, JS, images
├─ config/           # DB and app configuration
├─ controllers/      # Business logic (Auth, Payment)
├─ models/           # Data models (Course, Event, …)
├─ vendor/           # Composer packages
├─ views/            # Reusable UI components and pages
├─ seed_large.php    # Generates 10+ mock records per module
└─ README.md         # You are here!
```

## 🎯 Usage
- Public pages: `index.php`, `courses.php`, `webinars.php`, `events.php`, `support.php`
- Admin login: `admin/login.php`
- After logging in as admin, you can manage all entities via the dashboard.

## 📦 Deployment (Hostinger Basic)
1. Zip the project folder and upload it via **File Manager**.
2. Extract the archive into `public_html` (or a sub‑folder).
3. Create a MySQL database via **cPanel → MySQL Databases** and import `schema.sql`.
4. Update `config/config.php` with the new DB credentials and `SITE_URL`.
5. Set the **Document Root** (if you placed the app in a sub‑folder, e.g., `public_html/lms`).
6. Ensure the `uploads/` folder has write permissions (`chmod 755`).
7. Visit your site – the admin panel is reachable at `<your‑domain>/admin/login.php`.

## 🤝 Contributing
Feel free to open issues or submit pull requests. Please follow these steps:
1. Fork the repo.
2. Create a feature branch (`git checkout -b feature/awesome‑feature`).
3. Commit your changes and push to your fork.
4. Open a Pull Request against `main`.

## 📄 License
This project is licensed under the **MIT License** – see the `LICENSE` file for details.

---
*Enjoy building with CAVA LMS!*
