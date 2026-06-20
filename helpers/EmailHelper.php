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
    
    private static function logToDatabase($recipient, $subject, $body, $status, $errorMessage = null) {
        try {
            $db = DB::getConnection();
            $sql = "INSERT INTO email_logs (recipient, subject, body_excerpt, status, error_message) VALUES (?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $excerpt = substr(strip_tags($body), 0, 200);
            $stmt->execute([$recipient, $subject, $excerpt, $status, $errorMessage]);
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
