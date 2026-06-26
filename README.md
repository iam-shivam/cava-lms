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

## 🛠️ Local Development Setup
1. **Start XAMPP Services**
   - Open the **XAMPP Control Panel** and click **Start** for **Apache** and **MySQL**.
2. **Clone the Repository** (if you haven't already):
   ```bash
   git clone https://github.com/iam-shivam/cava-lms.git
   cd cava-lms
   ```
3. **Install PHP Dependencies**
   ```bash
   composer install
   ```

## 🚀 Running the Project

1. **Start XAMPP Services** (Apache & MySQL).
2. Open your browser and navigate to `http://localhost/lms/init_db.php` to create the database and seed mock data.
3. Verify the installation by visiting:
   - Landing page: `http://localhost/lms/index.php`
   - User dashboard: `http://localhost/lms/dashboard.php`
   - Admin panel: `http://localhost/lms/admin/index.php` (default admin: `admin@cava.com` / `AdminPassword123!`).


## ⚙️ Configuration
1. Copy `config/config.example.php` to `config/config.php`.
2. Edit the following constants in `config/config.php`:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - `SITE_URL` – base URL of the application (e.g., `http://localhost/lms`)
   - Razorpay keys – `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET`
3. Ensure the `uploads/` directory is writable by the web server.
## 🛠️ Local Setup & Run Instructions

### Option 1: Using XAMPP (Recommended)
1. **Copy Project Folder**: Copy the `cava-lms` folder into `C:\xampp\htdocs\lms`.
2. **Start Services**: Open XAMPP Control Panel and start **Apache** and **MySQL**, or run these commands in separate **cmd** windows:
   * **Start MySQL**:
     ```cmd
     C:\xampp\mysql_start.bat
     ```
   * **Start Apache**:
     ```cmd
     C:\xampp\apache_start.bat
     ```
3. **Configure Database**:
   - Ensure the configuration file `config/config.php` has the correct database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_PORT', '3307'); // Default is 3306; update if XAMPP MySQL is on 3307
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'cava_lms');
     define('SITE_URL', 'http://localhost/lms');
     ```
4. **Initialize Database**:
   - In your browser, navigate to: `http://localhost/lms/init_db.php`
   - You should see `Database Initialization Completed Successfully!`.
5. **Access Portal**:
   - **User Login**: `http://localhost/lms/login.php`
   - **Admin Login**: `http://localhost/lms/admin/login.php` (Credentials: `admin@cava.com` / `AdminPassword123!`)

### Option 2: Using PHP Built-in Server
1. **Configure SITE_URL**:
   - In `config/config.php`, change `SITE_URL` to:
     ```php
     define('SITE_URL', 'http://localhost:8000');
     ```
2. **Initialize Database**:
   - Run the initialization script using PHP CLI:
     ```bash
     php init_db.php
     ```
3. **Start Server**:
   - Start the built-in server in the project root:
     ```bash
     php -S localhost:8000
     ```
4. **Access Portal**:
   - **User Login**: `http://localhost:8000/login.php`
   - **Admin Login**: `http://localhost:8000/admin/login.php` (Credentials: `admin@cava.com` / `AdminPassword123!`)

## ⚙️ Additional Configuration
1. Ensure the `uploads/` directory is writable by the web server.
2. Edit Razorpay keys (`RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET`) in `config/config.php` if needed.

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
## Start The Project

- **Option 1: Use XAMPP Apache (recommended)**
  1. Open **XAMPP Control Panel** and click **Start** for **Apache** and **MySQL**.
  2. In your browser, navigate to `http://localhost/lms/` (or `http://localhost/` if the project resides in the web root).

- **Option 2: Use PHP built‑in server**
  ```bash
  "C:\\xampp\\php\\php.exe" -S localhost:8000 -t .
  ```
  Run the above command from the project root (`c:\xampp\htdocs\lms`). Then open `http://localhost:8000/` in the browser.

Both methods will serve the application; the first uses Apache with full .htaccess support, the second is quick for testing.

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
