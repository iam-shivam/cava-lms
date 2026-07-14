<?php
// Production Database Initialization Script for CAVA LMS

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

try {
    // 1. Connect to MySQL Server (Using the predefined DB_NAME from config)
    // We do NOT use DROP DATABASE here because shared hosting panels do not allow script-based DB creation.
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Connected to database `" . DB_NAME . "` successfully.<br>";
    
    // 2. Read and run schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("schema.sql not found at $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Split SQL queries by semicolon
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    echo "Database tables created/verified successfully.<br>";
    
    // 3. Seed Default Admin Account
    $adminEmail = 'admin@cava.com';
    $adminUsername = 'admin';
    $adminPassword = 'AdminPassword123!';
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
        $adminId = generate_uuid();
        
        $insertAdmin = $pdo->prepare("INSERT INTO admins (id, username, email, password_hash) VALUES (?, ?, ?, ?)");
        $insertAdmin->execute([$adminId, $adminUsername, $adminEmail, $passwordHash]);
        
        echo "Default admin account created:<br>";
        echo "- Email: <b>$adminEmail</b><br>";
        echo "- Password: <b>$adminPassword</b><br>";
    } else {
        echo "Admin account `$adminEmail` already exists.<br>";
    }
    
    // 4. Seed Default Dynamic Settings
    $settings = [
        'site_title' => 'CAVA LMS Portal',
        'contact_email' => 'contact@cavalms.com',
        'contact_phone' => '+91 98765 43210',
        'about_us' => 'CAVA LMS is a premium e-learning portal designed to provide quality, affordable education for career development and skill enhancements.',
        'hero_title' => 'Upgrade Your Skills with CAVA LMS',
        'hero_subtitle' => 'Access high-quality courses, webinars, and masterclasses designed by industry experts to boost your career.'
    ];
    
    $insertSetting = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    
    foreach ($settings as $key => $val) {
        $insertSetting->execute([$key, $val, $val]);
    }
    echo "Default settings seeded successfully.<br>";
    
    // 5. Seed Default Email Templates
    $templates = [
        [
            'key' => 'welcome_email',
            'name' => 'Welcome & Registration Email',
            'subject' => 'Welcome to {{company_name}}',
            'placeholders' => 'company_logo,company_name,user_name,dashboard_url,support_email,website,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;">
<tr>
<td align="center" style="background:#6D28D9;padding:45px;">
<img src="{{company_logo}}" width="80" alt="{{company_name}}">
<h1 style="margin:20px 0 10px;color:#ffffff;font-size:32px;">
Welcome to {{company_name}}
</h1>
<p style="color:#E9D5FF;font-size:16px;margin:0;">
Empowering Your Global Career Journey
</p>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">
Hello {{user_name}},
</h2>
<p style="font-size:16px;line-height:28px;color:#555555;">
Thank you for joining <strong>{{company_name}}</strong>.
Your account has been successfully created and you are now ready to begin your learning journey.
</p>
<p style="font-size:16px;color:#555555;">
Inside your dashboard you can
</p>
<table width="100%" cellpadding="8">
<tr><td>✅ Access Premium Courses</td></tr>
<tr><td>✅ Join Live Webinars</td></tr>
<tr><td>✅ Download Study Materials</td></tr>
<tr><td>✅ Track Learning Progress</td></tr>
<tr><td>✅ Earn Certificates</td></tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:35px;">
<tr>
<td align="center">
<a href="{{dashboard_url}}"
style="
display:inline-block;
background:#6D28D9;
padding:16px 38px;
color:#ffffff;
text-decoration:none;
border-radius:8px;
font-size:16px;
font-weight:bold;">
Go To Dashboard
</a>
</td>
</tr>
</table>
<div style="margin-top:40px;background:#F5F3FF;padding:20px;border-left:4px solid #6D28D9;border-radius:6px;">
<h3 style="margin-top:0;color:#6D28D9;">
Getting Started
</h3>
<p style="margin:0;line-height:28px;color:#555555;">
• Complete your profile.<br>
• Enroll in your first course.<br>
• Join upcoming live webinars.<br>
• Stay connected with our expert mentors.
</p>
</div>
</td>
</tr>
<tr>
<td style="background:#111827;padding:35px;text-align:center;">
<h3 style="margin-top:0;color:#ffffff;">
{{company_name}}
</h3>
<p style="color:#D1D5DB;line-height:26px;">
Empowering Your Global Career Journey
</p>
<p style="color:#9CA3AF;line-height:28px;">
📧 {{support_email}}<br>
🌐 {{website}}<br>
📞 {{phone}}
</p>
<p style="color:#6B7280;font-size:13px;margin-top:25px;">
© {{current_year}} {{company_name}}. All Rights Reserved.
</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'course_purchase',
            'name' => 'Course Purchase Confirmation',
            'subject' => 'Course Purchased: {{course_title}}',
            'placeholders' => 'user_name,course_title,dashboard_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Purchased</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td align="center" style="background:#6D28D9;padding:40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:28px;">Course Purchased Successfully</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
                            <p style="font-size:16px;line-height:26px;color:#555555;">
                                Thank you for purchasing <strong>{{course_title}}</strong>.
                            </p>
                            <p style="font-size:16px;line-height:26px;color:#555555;">
                                Your access is now active, and you are ready to begin learning. Click the button below to go to your dashboard and start immediately!
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px; margin-bottom:30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{dashboard_url}}" style="display:inline-block;background:#6D28D9;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Start Learning Now</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
                            <h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
                            <p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
                            <p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
        ],
        [
            'key' => 'webinar_registration',
            'name' => 'Webinar Registration Confirmation',
            'subject' => 'Webinar Registration: {{webinar_title}}',
            'placeholders' => 'user_name,webinar_title,webinar_date,webinar_time,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webinar Registration</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td align="center" style="background:#6D28D9;padding:40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:28px;">Webinar Registration Confirmed</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
                            <p style="font-size:16px;line-height:26px;color:#555555;">
                                You have successfully registered for the webinar: <strong>{{webinar_title}}</strong>.
                            </p>
                            <div style="margin:20px 0;background:#F5F3FF;padding:20px;border-left:4px solid #6D28D9;border-radius:6px;">
                                <p style="margin:0 0 10px 0;font-size:16px;color:#555555;"><strong>Date:</strong> {{webinar_date}}</p>
                                <p style="margin:0;font-size:16px;color:#555555;"><strong>Time:</strong> {{webinar_time}}</p>
                            </div>
                            <p style="font-size:16px;line-height:26px;color:#555555;">
                                Make sure to mark your calendar. See you there!
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
                            <h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
                            <p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
                            <p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
        ],
        [
            'key' => 'video_otp',
            'name' => 'Video Access OTP',
            'subject' => 'Video Access OTP: {{otp}}',
            'placeholders' => 'user_name,video_title,otp,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Access OTP</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td align="center" style="background:#6D28D9;padding:40px;">
                            <h1 style="margin:0;color:#ffffff;font-size:28px;">Video Access OTP</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
                            <p style="font-size:16px;line-height:26px;color:#555555;">
                                Your OTP to unlock the video \'<strong>{{video_title}}</strong>\' is:
                            </p>
                            <div style="margin:30px 0; text-align:center;">
                                <span style="display:inline-block; background:#F5F3FF; border:1px dashed #6D28D9; padding:15px 40px; font-size:32px; font-weight:bold; letter-spacing:5px; color:#6D28D9; border-radius:8px;">{{otp}}</span>
                            </div>
                            <p style="font-size:14px;color:#777777;text-align:center;margin:0;">
                                This OTP is valid for 5 minutes and can only be used once.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
                            <h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
                            <p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
                            <p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>'
        ],
        [
            'key' => 'reminder_24h',
            'name' => '24-Hour Webinar Reminder',
            'subject' => 'Reminder: {{webinar_title}} is in 24 hours!',
            'placeholders' => 'user_name,webinar_title,webinar_date,webinar_time,webinar_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>24-Hour Webinar Reminder</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#6D28D9;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">24-Hour Reminder</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
This is a quick reminder that the webinar <strong>{{webinar_title}}</strong> you registered for is starting in 24 hours.
</p>
<div style="margin:20px 0;background:#F5F3FF;padding:20px;border-left:4px solid #6D28D9;border-radius:6px;">
<p style="margin:0 0 10px 0;font-size:16px;color:#555555;"><strong>Date:</strong> {{webinar_date}}</p>
<p style="margin:0;font-size:16px;color:#555555;"><strong>Time:</strong> {{webinar_time}}</p>
</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
<tr>
<td align="center">
<a href="{{webinar_url}}" style="display:inline-block;background:#6D28D9;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Join Webinar Link</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'reminder_1h',
            'name' => '1-Hour Webinar Reminder',
            'subject' => 'Reminder: {{webinar_title}} starts in 1 hour!',
            'placeholders' => 'user_name,webinar_title,webinar_time,webinar_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>1-Hour Webinar Reminder</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#6D28D9;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Starting in 1 Hour</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
Get ready! The webinar <strong>{{webinar_title}}</strong> starts in just 1 hour.
</p>
<div style="margin:20px 0;background:#F5F3FF;padding:20px;border-left:4px solid #6D28D9;border-radius:6px;">
<p style="margin:0;font-size:16px;color:#555555;"><strong>Starts At:</strong> {{webinar_time}}</p>
</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
<tr>
<td align="center">
<a href="{{webinar_url}}" style="display:inline-block;background:#6D28D9;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Join Webinar Now</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'reminder_15m',
            'name' => '15-Minute Webinar Reminder',
            'subject' => 'Hurry! {{webinar_title}} is starting in 15 minutes',
            'placeholders' => 'user_name,webinar_title,webinar_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>15-Minute Webinar Reminder</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#6D28D9;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Starting in 15 Minutes</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
The webinar <strong>{{webinar_title}}</strong> is about to begin. Grab your coffee and join using the button below.
</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:35px;">
<tr>
<td align="center">
<a href="{{webinar_url}}" style="display:inline-block;background:#6D28D9;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Enter Webinar Room</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'live_now',
            'name' => 'Webinar Live Now Notification',
            'subject' => 'LIVE NOW: {{webinar_title}}',
            'placeholders' => 'user_name,webinar_title,webinar_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live Now</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#DC2626;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">🔴 WE ARE LIVE NOW</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
The webinar <strong>{{webinar_title}}</strong> has officially started. Click the button below to join the live session immediately!
</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:35px;">
<tr>
<td align="center">
<a href="{{webinar_url}}" style="display:inline-block;background:#DC2626;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Join Session Live</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'session_completed',
            'name' => 'Webinar Session Completed Feedback',
            'subject' => 'Thank you for attending: {{webinar_title}}',
            'placeholders' => 'user_name,webinar_title,feedback_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Session Completed</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#6D28D9;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Session Completed</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
Thank you for attending the webinar <strong>{{webinar_title}}</strong>. We hope you found it educational and inspiring!
</p>
<p style="font-size:16px;line-height:26px;color:#555555;">
We would love to get your feedback so we can continue to improve. Please take a minute to fill out our quick survey.
</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:35px;">
<tr>
<td align="center">
<a href="{{feedback_url}}" style="display:inline-block;background:#6D28D9;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Share Your Feedback</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'payment_success',
            'name' => 'Payment Success Confirmation',
            'subject' => 'Payment Success Confirmation: Receipt #{{receipt_id}}',
            'placeholders' => 'user_name,receipt_id,amount,payment_method,item_name,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Successful</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#059669;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Payment Successful</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
Your payment has been successfully processed. Here is your receipt summary:
</p>
<table width="100%" style="border-collapse:collapse;margin:25px 0;">
<tr style="border-bottom:1px solid #E5E7EB;"><td style="padding:10px 0;color:#6B7280;">Item:</td><td style="padding:10px 0;text-align:right;font-weight:bold;color:#111827;">{{item_name}}</td></tr>
<tr style="border-bottom:1px solid #E5E7EB;"><td style="padding:10px 0;color:#6B7280;">Amount Paid:</td><td style="padding:10px 0;text-align:right;font-weight:bold;color:#059669;">₹{{amount}}</td></tr>
<tr style="border-bottom:1px solid #E5E7EB;"><td style="padding:10px 0;color:#6B7280;">Receipt ID:</td><td style="padding:10px 0;text-align:right;font-weight:bold;color:#111827;">{{receipt_id}}</td></tr>
<tr style="border-bottom:1px solid #E5E7EB;"><td style="padding:10px 0;color:#6B7280;">Payment Method:</td><td style="padding:10px 0;text-align:right;font-weight:bold;color:#111827;">{{payment_method}}</td></tr>
</table>
<p style="font-size:14px;color:#6B7280;margin:0;">
Thank you for your purchase! A PDF version of your invoice will be available in your dashboard billing history.
</p>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'payment_failed',
            'name' => 'Payment Failed Alert',
            'subject' => 'Alert: Payment Failed for {{item_name}}',
            'placeholders' => 'user_name,item_name,amount,retry_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Failed</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#DC2626;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Payment Failed</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
We were unable to process your payment of <strong>₹{{amount}}</strong> for <strong>{{item_name}}</strong>. No charges were made to your account.
</p>
<p style="font-size:16px;line-height:26px;color:#555555;">
This could be due to insufficient funds, an expired card, or incorrect details. You can try the payment again using the link below:
</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:35px;">
<tr>
<td align="center">
<a href="{{retry_url}}" style="display:inline-block;background:#DC2626;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Retry Payment</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'newsletter',
            'name' => 'General Newsletter Template',
            'subject' => 'Monthly Update: {{newsletter_subject}}',
            'placeholders' => 'user_name,newsletter_title,newsletter_content,opt_out_url,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Newsletter</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#6D28D9;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">{{newsletter_title}}</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<div style="font-size:16px;line-height:28px;color:#555555;">
{{newsletter_content}}
</div>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0 0 15px 0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
<small style="color:#6B7280;">You received this email because you subscribed to our newsletter. If you wish to unsubscribe, click <a href="{{opt_out_url}}" style="color:#9CA3AF;text-decoration:underline;">here</a>.</small>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'contact_auto_reply',
            'name' => 'Contact Auto Reply Confirmation',
            'subject' => 'We received your message: Ticket #{{ticket_id}}',
            'placeholders' => 'user_name,ticket_id,message_excerpt,company_name,support_email,phone,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auto Reply</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#6D28D9;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Message Received</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">Hello {{user_name}},</h2>
<p style="font-size:16px;line-height:26px;color:#555555;">
Thank you for contacting <strong>{{company_name}}</strong>. We have received your query and assigned ticket number <strong>#{{ticket_id}}</strong>.
</p>
<p style="font-size:16px;line-height:26px;color:#555555;">
Our support team is reviewing your message and will respond as soon as possible (usually within 24 business hours).
</p>
<div style="margin:20px 0;background:#F5F3FF;padding:15px;border-left:4px solid #6D28D9;border-radius:6px;font-style:italic;color:#6D28D9;font-size:14px;">
"{{message_excerpt}}"
</div>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0 0 5px 0;">📧 {{support_email}} | 📞 {{phone}}</p>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ],
        [
            'key' => 'admin_notification',
            'name' => 'Admin System Notification',
            'subject' => 'System Notification: {{alert_title}}',
            'placeholders' => 'alert_title,alert_message,alert_level,action_url,company_name,current_year',
            'body' => '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Notification</title>
</head>
<body style="margin:0;padding:0;background:#F4F5F7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F5F7;padding:40px 15px;">
<tr>
<td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
<tr>
<td align="center" style="background:#1F2937;padding:40px;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Admin Alert System</h1>
</td>
</tr>
<tr>
<td style="padding:40px;">
<h2 style="margin-top:0;color:#111827;">{{alert_title}}</h2>
<div style="margin:15px 0;padding:10px 15px;background:#FEF3C7;border-left:4px solid #F59E0B;border-radius:4px;color:#D97706;font-weight:bold;font-size:14px;">
Priority: {{alert_level}}
</div>
<p style="font-size:16px;line-height:26px;color:#555555;">
{{alert_message}}
</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:35px;">
<tr>
<td align="center">
<a href="{{action_url}}" style="display:inline-block;background:#111827;padding:14px 30px;color:#ffffff;text-decoration:none;border-radius:8px;font-size:16px;font-weight:bold;">Review in Dashboard</a>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="background:#111827;padding:30px;text-align:center;color:#D1D5DB;font-size:14px;">
<h3 style="margin:0 0 10px 0;color:#ffffff;">{{company_name}}</h3>
<p style="margin:0;color:#9CA3AF;font-size:12px;">&copy; {{current_year}} {{company_name}}. All Rights Reserved.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>'
        ]
    ];
    
    $insertTemplate = $pdo->prepare("
        INSERT INTO email_templates (id, template_key, name, subject, body, placeholders)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name = ?, subject = ?, body = ?, placeholders = ?
    ");
    
    foreach ($templates as $t) {
        $uuid = generate_uuid();
        $insertTemplate->execute([
            $uuid, $t['key'], $t['name'], $t['subject'], $t['body'], $t['placeholders'],
            $t['name'], $t['subject'], $t['body'], $t['placeholders']
        ]);
    }
    echo "Default email templates seeded successfully.<br>";
    
    echo "<h3>Production Database Initialization Completed Successfully!</h3>";
    echo "<p style='color:red; font-weight:bold;'>SECURITY WARNING: Please delete this init_db.php file from your server after running it!</p>";

} catch (Exception $e) {
    echo "<h3>Initialization Failed:</h3> " . $e->getMessage();
}
