<?php
// Email Helper Utility using PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    
    public static function sendEmail($recipientEmail, $recipientName, $subject, $body) {
        // Ensure autoload works
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            self::logToDatabase($recipientEmail, $subject, $body, 'Failed', 'PHPMailer library not loaded');
            return false;
        }
        
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            
            if (SMTP_SECURE === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mail->Port       = SMTP_PORT;
            
            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($recipientEmail, $recipientName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            
            self::logToDatabase($recipientEmail, $subject, $body, 'Sent');
            return true;
        } catch (Exception $e) {
            $errorMsg = "PHPMailer Error: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage();
            self::logToDatabase($recipientEmail, $subject, $body, 'Failed', $errorMsg);
            return false;
        }
    }
    
    public static function sendTemplateEmail($recipientEmail, $recipientName, $templateKey, $customPlaceholders = []) {
        try {
            $db = DB::getConnection();
            $stmt = $db->prepare("SELECT * FROM email_templates WHERE template_key = ?");
            $stmt->execute([$templateKey]);
            $template = $stmt->fetch();
            
            // If template doesn't exist, fall back to a generic fallback or return false
            if (!$template) {
                // Build a fallback simple email
                $subject = "Notification: " . ucwords(str_replace('_', ' ', $templateKey));
                $body = "<h3>Hello " . htmlspecialchars($recipientName) . ",</h3><p>This is a system notification for: " . htmlspecialchars($templateKey) . "</p>";
                if (!empty($customPlaceholders)) {
                    $body .= "<ul>";
                    foreach ($customPlaceholders as $k => $v) {
                        $body .= "<li><strong>" . htmlspecialchars($k) . ":</strong> " . htmlspecialchars($v) . "</li>";
                    }
                    $body .= "</ul>";
                }
                return self::sendEmail($recipientEmail, $recipientName, $subject, $body);
            }
            
            // Fetch global settings
            $settings = [];
            try {
                $settingsRows = DB::fetchAll("SELECT setting_key, setting_value FROM settings");
                foreach ($settingsRows as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            } catch (\Exception $ex) {
                // Ignore settings errors, use defaults
            }
            
            // Build default global placeholders
            $globalPlaceholders = [
                'company_name' => $settings['site_title'] ?? 'CAVA LMS Portal',
                'support_email' => $settings['contact_email'] ?? 'support@cavalms.com',
                'phone' => $settings['contact_phone'] ?? '+91 98765 43210',
                'website' => SITE_URL,
                'current_year' => date('Y'),
                'dashboard_url' => SITE_URL . '/login.php',
                'company_logo' => SITE_URL . '/assets/images/logo.png'
            ];
            
            // Merge custom placeholders over global ones
            $placeholders = array_merge($globalPlaceholders, $customPlaceholders);
            
            // Process subject and body placeholders
            $subject = $template['subject'];
            $body = $template['body'];
            
            foreach ($placeholders as $key => $value) {
                $placeholderStr = '{{' . $key . '}}';
                $subject = str_replace($placeholderStr, $value ?? '', $subject);
                $body = str_replace($placeholderStr, $value ?? '', $body);
            }
            
            // Send email using primary sendEmail function
            return self::sendEmail($recipientEmail, $recipientName, $subject, $body);
            
        } catch (\Exception $e) {
            $errorMsg = "Template send error for " . $templateKey . ": " . $e->getMessage();
            self::logToDatabase($recipientEmail, 'Template Error: ' . $templateKey, 'Error during template compilation', 'Failed', $errorMsg);
            return false;
        }
    }

    private static function logToDatabase($recipient, $subject, $body, $status, $errorMessage = null) {
        try {
            $db = DB::getConnection();
            $uuid = generate_uuid();
            $sql = "INSERT INTO email_logs (id, recipient, subject, body_excerpt, status, error_message) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $excerpt = substr(strip_tags($body), 0, 200);
            $stmt->execute([$uuid, $recipient, $subject, $excerpt, $status, $errorMessage]);
        } catch (\PDOException $e) {
            // Fail silently
        }
    }
    
    // Background/Cron function to process pending email logs
    public static function processPendingEmails() {
        try {
            $pending = DB::fetchAll("SELECT * FROM email_logs WHERE status = 'Pending' LIMIT 10");
            foreach ($pending as $log) {
                // To avoid loops we construct generic email body, or we can save body in email_logs.
                // In our current implementation, we just mark as sent or trigger. 
                // Let's send the email and update:
                $user = DB::fetch("SELECT full_name FROM users WHERE email = ?", [$log['recipient']]);
                $name = $user ? $user['full_name'] : 'User';
                
                $sent = self::sendEmail($log['recipient'], $name, $log['subject'], $log['body_excerpt']);
                if ($sent) {
                    DB::query("DELETE FROM email_logs WHERE id = ?", [$log['id']]);
                } else {
                    DB::query("UPDATE email_logs SET status = 'Failed', error_message = 'Failed during cron processing' WHERE id = ?", [$log['id']]);
                }
            }
        } catch (Exception $e) {
            // Silently ignore
        }
    }
}
