# Local Setup Guide for CAVA LMS

This guide explains how to set up the CAVA LMS project locally on your machine, highlighting the new passwordless OTP login system.

---

## Part 1: Prerequisites
Make sure you have the following installed on your local machine:
- **XAMPP** (with PHP 7.4+ or 8.x, and MySQL)
- **Composer** (optional, but a pre-packaged `composer.phar` is included in the project)

---

## Part 2: Step-by-Step Installation

### Step 1: Copy Project Files
Place the project files inside your XAMPP web root directory:
```
C:\xampp\htdocs\lms\
```

### Step 2: Install Composer Dependencies
Open your terminal (Command Prompt, PowerShell, or Git Bash), navigate to the project directory, and run the following command to download the required libraries and regenerate the autoloader:
```bash
# Using local php and composer.phar
C:\xampp\php\php.exe composer.phar install
```

### Step 3: Configure Environment Variables (`.env`)
Create a `.env` file in the project root (`C:\xampp\htdocs\lms\.env`). You can use the template below:
```env
# Application URL
SITE_URL=http://localhost/lms

# Database Setup
DB_HOST=127.0.0.1
DB_USER=root
DB_PASS=
DB_NAME=cava_lms

# SMTP Email Credentials (e.g. Gmail App Password)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=465
SMTP_USER=your-email@gmail.com
SMTP_PASS="xxxx xxxx xxxx xxxx" # Wrap in double quotes if it has spaces
SMTP_SECURE=ssl
SMTP_FROM_EMAIL=no-reply@cavalms.com
SMTP_FROM_NAME="CAVA LMS Portal"

# Master OTP (Use this code to log in instantly without checking email)
MASTER_OTP=123456
```

### Step 4: Initialize the Database
1. Start **Apache** and **MySQL** in your XAMPP Control Panel.
2. Open your web browser and navigate to:
   [http://localhost/lms/init_db.php](http://localhost/lms/init_db.php)
3. You will see a success message: `Database Initialization Completed Successfully!`. This will automatically:
   - Create the `cava_lms` database.
   - Create all tables (including the new `password_resets` table for OTP tracking).
   - Seed default settings, demo courses, webinars, and admin accounts.

---

## Part 3: Understanding the Auth Flow & Changes

1. **Passwordless Login**:
   - Navigate to [http://localhost/lms/login.php](http://localhost/lms/login.php).
   - Enter your email address or mobile number.
   - You will be redirected to the OTP verification page. Enter the code sent to your email, or simply enter the `MASTER_OTP` (e.g., `123456`) specified in your `.env` file to log in instantly.
2. **Passwordless Registration**:
   - Users can register at [http://localhost/lms/register.php](http://localhost/lms/register.php) with just their **Full Name, Email, and Mobile Number**. No passwords are required during sign-up.
3. **No Google Login**:
   - Google login integrations have been fully removed from both user and admin login pages to keep the interface clean and secure.
4. **No Password Forms in Profile Settings**:
   - Users can update their profile information on the dashboard, but password changing forms have been removed since logins are exclusively OTP-driven.
5. **Admin Access**:
   - Admin panel remains at [http://localhost/lms/admin/login.php](http://localhost/lms/admin/login.php). Admins continue to log in using standard username/email and passwords for security.
   - Default Admin Credentials:
     - **Email / Username**: `admin@cava.com` (or `admin`)
     - **Password**: `AdminPassword123!`
