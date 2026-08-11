<h1 align="center">
  <br>
  🎓 CAVA LMS Portal
  <br>
</h1>

<p align="center">
  A modern, full-featured <strong>Learning Management System</strong> built with Core PHP 8, MySQL, Bootstrap 5, and Razorpay — deployable on any standard shared hosting with cPanel.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" />
  <img src="https://img.shields.io/badge/Razorpay-Payment-0D9BD4?style=for-the-badge&logo=razorpay&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" />
</p>

---

## 📖 Table of Contents

- [About the Project](#-about-the-project)
- [Tech Stack](#-tech-stack)
- [Features](#-features)
- [Folder Structure](#-folder-structure)
- [Database Schema](#-database-schema)
- [Third-Party Integrations](#-third-party-integrations)
- [Environment Variables](#-environment-variables)
- [Local Setup (XAMPP)](#-local-setup-xampp)
- [Deployment (Shared Hosting / cPanel)](#-deployment-shared-hosting--cpanel)
- [Admin Panel](#-admin-panel)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🚀 About the Project

**CAVA LMS Portal** is a production-ready Learning Management System built for institutions and educators who want to sell courses, host webinars, and manage students — all from a single, clean admin dashboard.

> 💡 Inspired by the TagMango UI/UX. Built without Laravel, WordPress, Node.js, Docker, or Redis — pure PHP, MySQL, and Bootstrap.

**Key Highlights:**
- Sell courses with full/partial payment support
- Host and sell paid webinars
- Manage students, enrollments, and payments from the admin panel
- Email notifications via PHPMailer (Gmail SMTP)
- Bunny Stream CDN integration for secure video delivery
- Google OAuth login support
- OTP-based login via email
- Video progress tracking per user
- Fully responsive, mobile-friendly UI

---

## 🛠 Tech Stack

### Backend
| Technology | Version | Purpose |
|---|---|---|
| **PHP** | 8.0+ | Core application logic |
| **MySQL** | 8.0 | Relational database |
| **PDO** | Built-in | Secure database queries |
| **Apache** | 2.4+ | Web server |
| **Composer** | 2.x | PHP dependency manager |

### Frontend
| Technology | Version | Purpose |
|---|---|---|
| **Bootstrap** | 5.x | Responsive UI framework |
| **HTML5** | — | Page structure |
| **CSS3** | — | Custom styling & animations |
| **JavaScript (Vanilla)** | ES6+ | Client-side interactivity |

### PHP Libraries (via Composer)
| Package | Version | Purpose |
|---|---|---|
| `phpmailer/phpmailer` | ^6.8 | Email notifications via SMTP |
| `razorpay/razorpay` | * | Payment gateway integration |
| `vlucas/phpdotenv` | * | `.env` file support |

### External Services
| Service | Purpose |
|---|---|
| **Razorpay** | Payment gateway (INR) |
| **Bunny Stream CDN** | Secure video hosting & delivery |
| **Google OAuth 2.0** | Social login |
| **Gmail SMTP** | Transactional emails |
| **Google Sheets Webhook** | Lead capture via Apps Script |

---

## ✨ Features

### 🌐 Public Website
- **Landing Page** — Hero banner, About section, Featured Courses, Upcoming Webinars, Events, Testimonials, Contact Form, Footer
- **Course Catalog** — Browse all courses with category filters
- **Webinars Page** — View and register for upcoming paid/free webinars
- **Events Page** — View upcoming events

### 🔐 Authentication
- Email + Password registration & login
- **Google OAuth** social login
- **OTP-based login** via email (with rate limiting)
- Secure session-based authentication
- Password reset via email OTP
- Login attempt tracking & brute-force protection

### 📚 Course System
- Course categories with slugs
- Courses with sections and individual video lessons
- Sortable sections and videos
- YouTube, Vimeo, and Bunny Stream CDN video support
- **Video access duration control** per lesson
- Locked/unlocked state based on enrollment
- **Video progress tracking** per user
- Document attachments per video lesson
- Secure PHP-served document viewer

### 💳 Payment System
- **Razorpay** integration with server-side order creation
- Supports **full payment** and **partial/installment payments**
- Payments for both Courses and Webinars
- Payment status: `Pending`, `Success`, `Failed`
- Auto-enrollment on successful payment
- Enrollment expiry date management

### 📹 Webinar Module
- Admin creates webinars with title, description, date, time, price
- Users register and pay for webinars
- Admin can export registrant list to CSV

### 📅 Events Module
- Admin adds events with title, description, date, image
- Users view all upcoming events on the public page

### 📊 User Dashboard
- Welcome message & profile overview
- **My Courses** — enrolled courses with video progress
- **My Webinars** — registered webinars
- **Payment History** — full/partial payment records
- **Query History** — submitted support queries
- Profile edit with avatar upload

### 📧 Email Notifications
- Fully customizable **email templates** via admin panel
- Triggered for: User Registration, Course Purchase, Webinar Registration, OTP Login
- Email delivery logs stored in database
- Powered by **PHPMailer** with Gmail SMTP

### 🗣 Support Queries
- Users submit queries (Name, Email, Mobile, Message)
- Admin views and marks queries as Resolved

---

## 📁 Folder Structure

```
lms/
├── admin/                    # Admin panel pages
│   ├── dashboard.php
│   ├── courses.php
│   ├── videos.php
│   ├── video_edit.php
│   ├── section_edit.php
│   ├── categories.php
│   ├── users.php
│   ├── enrollments.php
│   ├── payments.php
│   ├── webinars.php
│   ├── events.php
│   ├── queries.php
│   ├── email_templates.php
│   ├── email_template_add.php
│   ├── email_template_edit.php
│   ├── settings.php
│   ├── login.php / logout.php
│   ├── admin_header.php / admin_footer.php
│   └── export_registrants.php
│
├── api/                      # Internal AJAX / API endpoints
│   ├── bunny_upload.php      # Bunny Stream video upload
│   ├── track_progress.php    # Video progress tracker
│   └── video_otp.php         # OTP-secured video access
│
├── assets/
│   ├── css/                  # Custom stylesheets
│   ├── js/                   # Custom JavaScript
│   └── images/               # Static images & icons
│
├── config/
│   ├── config.php            # App config (loads .env, constants)
│   └── db.php                # PDO database connection class
│
├── controllers/
│   ├── AuthController.php    # Registration, login, OTP, Google OAuth
│   └── PaymentController.php # Razorpay order creation & verification
│
├── helpers/                  # Utility / helper functions
├── migrations/               # Database migration scripts
│
├── models/
│   ├── Admin.php
│   ├── Course.php
│   ├── Event.php
│   ├── Payment.php
│   ├── Query.php
│   ├── User.php
│   ├── VideoOTP.php
│   └── Webinar.php
│
├── scripts/                  # CLI / utility scripts
│
├── services/
│   └── BunnyStreamService.php   # Bunny CDN API wrapper
│
├── uploads/                  # User-uploaded files (thumbnails, documents)
├── vendor/                   # Composer dependencies (auto-generated)
│
├── views/
│   ├── components/
│   │   ├── course_card.php
│   │   └── webinar_card.php
│   └── layout/
│       ├── header.php        # Global navbar / header
│       └── footer.php        # Global footer
│
├── .env                      # Environment variables (DO NOT commit)
├── .env.example              # Safe-to-commit template
├── .gitignore
├── .htaccess                 # URL rewrites & security rules
├── composer.json
├── schema.sql                # Full MySQL database schema
├── init_db.php               # DB initializer
├── seed.php                  # Database seeder with demo data
│
├── index.php                 # Public landing page
├── login.php
├── register.php
├── logout.php
├── dashboard.php             # User dashboard
├── courses.php               # Course catalog
├── course.php                # Single course details
├── course_play.php           # Video player page
├── webinars.php
├── events.php
├── support.php               # Contact / query form
├── document_viewer.php       # Secure in-browser document viewer
├── serve_document.php        # PHP file server (blocks direct URL access)
├── video_stream.php          # Video stream handler
├── payment_process.php       # Payment initiation
├── payment_callback.php      # Razorpay callback / webhook
├── otp_login.php
├── otp_verify.php
├── resend_otp.php
├── google_login.php          # Google OAuth redirect
└── 404.php                   # Custom 404 error page
```

---

## 🗄 Database Schema

| Table | Description |
|---|---|
| `admins` | Admin accounts with hashed passwords |
| `users` | Registered student accounts |
| `categories` | Course categories with URL slugs |
| `courses` | Course listings (price, status, installment config) |
| `course_sections` | Ordered sections within a course |
| `course_videos` | Individual video lessons per section |
| `video_documents` | File/document attachments per lesson |
| `payments` | All Razorpay payment records |
| `enrollments` | Course enrollment with expiry tracking |
| `webinars` | Webinar listings |
| `webinar_registrations` | Webinar signups per user |
| `events` | Events with images |
| `queries` | User support queries |
| `settings` | Key-value site settings (hero text, logo, etc.) |
| `email_templates` | Admin-editable email templates |
| `email_logs` | Log of all emails sent |
| `password_resets` | OTP tokens for password reset |
| `video_otp_sessions` | Temporary OTP sessions for secure video |
| `user_video_progress` | Per-user video watch tracking |
| `otp_requests` | OTP request rate limiting |
| `login_attempts` | Brute-force protection log |

> **Import `schema.sql`** via phpMyAdmin to create all tables at once.

---

## 🔌 Third-Party Integrations

### 💳 Razorpay
- Server-side order creation via official PHP SDK
- Payment verified using HMAC SHA256 signature
- Supports INR currency and partial installment payments
- Manage keys at: [dashboard.razorpay.com](https://dashboard.razorpay.com)

### 📹 Bunny Stream CDN
- Videos uploaded directly to Bunny Stream library via REST API
- Secure CDN delivery with signed URLs
- Admin panel upload interface included
- Get API keys at: [bunny.net](https://bunny.net)

### 🔑 Google OAuth 2.0
- One-click login with Google accounts
- Requires a project on [Google Cloud Console](https://console.cloud.google.com)
- Set Authorized Redirect URI: `https://yourdomain.com/google_callback.php`

### 📧 PHPMailer (Gmail SMTP)
- All transactional emails sent via Gmail with App Password
- SSL on port 465 / TLS on port 587
- Fully customizable templates editable from admin panel

### 📊 Google Sheets Webhook
- Optionally pushes query/lead data to a Google Sheet
- Configured via a Google Apps Script deployment URL

---

## ⚙️ Environment Variables

Copy `.env.example` to `.env` and fill in your values:

```env
# Application
SITE_URL=http://localhost/lms

# Database
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=cava_lms

# Razorpay
RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxx

# SMTP (Gmail)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=465
SMTP_USER=your@gmail.com
SMTP_PASS=your_app_password
SMTP_SECURE=ssl
SMTP_FROM_EMAIL=no-reply@yourdomain.com
SMTP_FROM_NAME="Your LMS Name"

# Google OAuth
GOOGLE_CLIENT_ID=xxxxxxxxxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxx
GOOGLE_REDIRECT_URI=${SITE_URL}/google_callback.php

# OTP (for development testing)
MASTER_OTP=123456

# Google Sheets Webhook
GOOGLE_SHEETS_WEBHOOK=https://script.google.com/macros/s/your_script/exec

# Bunny Stream CDN
BUNNY_STREAM_LIBRARY_ID=000000
BUNNY_STREAM_API_KEY=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
BUNNY_STREAM_CDN_HOSTNAME=your-cdn.b-cdn.net
```

> ⚠️ **Never commit your `.env` file to version control.** It is already listed in `.gitignore`.

---

## 💻 Local Setup (XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with PHP 8.0+, MySQL, Apache
- [Composer](https://getcomposer.org/) installed globally
- [Git](https://git-scm.com/) (optional)

### Steps

```bash
# 1. Clone or download the repository
git clone https://github.com/your-username/cava-lms.git C:/xampp/htdocs/lms

# 2. Install PHP dependencies
cd C:/xampp/htdocs/lms
composer install

# 3. Set up environment variables
copy .env.example .env
# Open .env and fill in your database credentials and API keys

# 4. Start XAMPP — enable Apache + MySQL

# 5. Create the database
#    Open: http://localhost/phpmyadmin
#    Create a new database named: cava_lms

# 6. Import the schema
#    phpMyAdmin → cava_lms → Import → select schema.sql → Go

# 7. (Optional) Seed demo data
php seed.php

# 8. Open the app
#    http://localhost/lms
```

**Default Admin Login** (after running `seed.php`):
| Field | Value |
|---|---|
| URL | `http://localhost/lms/admin/` |
| Email | `admin@cavalms.com` |
| Password | `Admin@123` |

---

## 🚀 Deployment (Shared Hosting / cPanel)

1. **Upload** all project files to `public_html/` (or a subdirectory) via File Manager or FTP — **exclude the `vendor/` folder if running Composer via SSH**
2. **Create** a MySQL database in cPanel → MySQL Databases
3. **Create** a database user and assign full privileges to the database
4. **Import** `schema.sql` via phpMyAdmin
5. **Update `.env`** with production DB credentials, Razorpay **live** keys, Gmail SMTP, and the correct `SITE_URL`
6. **Set folder permissions**: `uploads/` should be `755` (writable by the server)
7. **Run Composer** (if SSH access is available):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
8. **Test** the site at your domain and the admin panel at `/admin/`

> For step-by-step details, see [`deployment_guide.md`](deployment_guide.md)

---

## 🔧 Admin Panel

Access at: `https://yourdomain.com/admin/` — requires a separate admin login.

| Module | Description |
|---|---|
| **Dashboard** | Overview stats: users, courses, revenue, recent payments & signups |
| **Users** | View all registered students, activate/suspend accounts |
| **Courses** | Create, edit, publish/draft courses with thumbnails |
| **Categories** | Manage course categories with slugs |
| **Videos** | Add/edit lessons, upload to Bunny Stream, attach documents |
| **Webinars** | Create/manage webinars, export registrant CSV |
| **Enrollments** | View all active and expired enrollments |
| **Payments** | Full payment history with status filter |
| **Queries** | View & mark user support queries as Resolved |
| **Events** | Create and manage events with images |
| **Email Templates** | Edit all transactional email content & subjects |
| **Settings** | Update hero title, about text, site logo, social links |

---

## 🔒 Security

| Measure | Implementation |
|---|---|
| **SQL Injection Prevention** | PDO prepared statements used everywhere |
| **XSS Protection** | `htmlspecialchars()` on all user-facing output |
| **Password Hashing** | `password_hash()` (bcrypt) + `password_verify()` |
| **Session Hardening** | Session ID regenerated on login/logout |
| **CSRF Protection** | Token-based form validation |
| **File Upload Validation** | MIME type + extension whitelist + size limits |
| **Brute Force Protection** | `login_attempts` table with per-IP rate limiting |
| **OTP Rate Limiting** | `otp_requests` table limiting attempts per IP/identifier |
| **Secure Video Access** | Time-limited OTP sessions for each video |
| **Secure File Serving** | Documents served via PHP, not directly accessible |
| **Environment Secrets** | All secrets in `.env`, never hardcoded in source |
| **Directory Lockdown** | `.htaccess` blocks direct access to sensitive directories |

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you'd like to change.

1. Fork the project
2. Create your feature branch: `git checkout -b feature/AmazingFeature`
3. Commit your changes: `git commit -m 'Add some AmazingFeature'`
4. Push to the branch: `git push origin feature/AmazingFeature`
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License**.

---

<p align="center">
  Made with ❤️ for educators and learners &nbsp;|&nbsp; <strong>CAVA LMS Portal</strong>
</p>
