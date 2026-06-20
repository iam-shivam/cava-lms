# CAVA LMS Portal - Run and Deployment Guide

This guide details how to run the LMS project locally using XAMPP and how to deploy it on a Hostinger Basic Hosting plan.

---

## Part 1: How to Run the Project Locally

Follow these steps to run the CAVA LMS Portal on your local computer using XAMPP:

### 1. Start XAMPP Services
1. Open the **XAMPP Control Panel** on your computer.
2. Click **Start** next to **Apache** and **MySQL**.

### 2. Verify Database Setup
We have seeded the database with mock records (Categories, Courses, Section lectures, Webinars, Events, and default Admin). If you need to re-run the database setup at any time:
1. Open your browser and navigate to: [http://localhost/lms/init_db.php](http://localhost/lms/init_db.php)
2. You should see a success message: `Database Initialization Completed Successfully!`.

### 3. Access Portal URLs
- **Public Website (Landing Page)**: [http://localhost/lms/index.php](http://localhost/lms/index.php)
- **User Dashboard**: [http://localhost/lms/dashboard.php](http://localhost/lms/dashboard.php)
- **Admin Panel Control**: [http://localhost/lms/admin/index.php](http://localhost/lms/admin/index.php)
  - **Default Admin Account**: `admin@cava.com` (or username `admin`)
  - **Default Admin Password**: `AdminPassword123!`

---

## Part 2: How to Deploy on Hostinger Basic Plan

Hostinger Basic Plan uses **hPanel** (Hostinger's custom, user-friendly alternative to cPanel). The deployment process is identical.

### Step 1: Export Local Database
1. Open local phpMyAdmin at [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2. Select the `cava_lms` database from the left sidebar.
3. Click the **Export** tab at the top.
4. Keep the export method as **Quick** and format as **SQL**, then click **Export**. Save the `.sql` file to your computer.

### Step 2: Create a MySQL Database on Hostinger
1. Log in to your Hostinger Control Panel (hPanel).
2. Navigate to **Databases** -> **MySQL Databases**.
3. Create a new database:
   - **MySQL Database Name**: e.g., `u123456789_lms`
   - **MySQL Username**: e.g., `u123456789_lmsuser`
   - **Password**: Create a strong password and save it somewhere secure.
4. Click **Create**.

### Step 3: Import SQL Schema on Hostinger
1. In Hostinger Databases page, locate your new database and click **Enter phpMyAdmin**.
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select the `.sql` file you exported in Step 1 (or upload [schema.sql](schema.sql)).
4. Click **Go** / **Import** at the bottom. This will create all 14 tables in your live database.

### Step 4: Package and Upload Project Files
1. Open your local `c:\xampp\htdocs\lms\` folder.
2. Select all files and folders (including `vendor`, `config`, `models`, `views`, `admin`, `assets`, `uploads`, and `.php` files). 
3. Right-click and compress them into a single **ZIP** archive (e.g. `cava_lms.zip`).
4. In Hostinger hPanel, go to **Files** -> **File Manager**.
5. Navigate into the **`public_html`** folder (this is the root directory of your website).
6. Click the **Upload** icon at the top right, select `cava_lms.zip`, and upload it.
7. Right-click the uploaded ZIP file inside the File Manager and select **Extract**. Extract all files directly into `public_html`.

### Step 5: Adjust Configuration File
1. In the Hostinger File Manager, open the **`config`** folder and right-click **`config.php`**, then select **Edit**.
2. Modify the configuration parameters to match your live hosting:

```php
// Update your live Website URL (replace yourdomain.com with your actual domain name)
define('SITE_URL', 'https://yourdomain.com'); 

// Update database credentials with the ones created in Step 2
define('DB_HOST', 'localhost'); // Usually localhost on Hostinger
define('DB_NAME', 'u123456789_lms'); // Your Hostinger DB Name
define('DB_USER', 'u123456789_lmsuser'); // Your Hostinger DB User
define('DB_PASS', 'YourStrongDBPassword'); // Your Hostinger DB Password
```
3. Save and close the file.

### Step 6: Adjust Directory Permissions
Make sure the folder where course thumbnails and event images are stored is writable by the server:
1. In Hostinger File Manager, right-click the **`uploads`** folder.
2. Select **Permissions**.
3. Set permissions to **`755`** (or `775`/`777` if required by the host for writes) and click **Update**.

### Step 7: Verify Live Deployment
1. Open your domain (e.g. `https://yourdomain.com`) in your browser.
2. The landing page should render correctly.
3. Access `https://yourdomain.com/admin/login.php` to verify the administrative portal works.
4. Try logging in, uploading a course thumbnail, and sending a mock query to ensure full database and filesystem write privileges are functional.
