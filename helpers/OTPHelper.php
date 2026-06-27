<?php
class OTPHelper {
    // Generate a numeric OTP of given length (default 6)
    public static function generateOTP(int $length = 6): string {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    // Store OTP in password_resets table with expiry
    public static function storeOTP(string $email, string $otp, int $expiryMinutes = 5): bool {
        $db = DB::getConnection();
        $stmt = $db->prepare('INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE)) ON DUPLICATE KEY UPDATE otp = ?, expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE)');
        return $stmt->execute([$email, $otp, $expiryMinutes, $otp, $expiryMinutes]);
    }

    // Verify OTP and check expiry
    public static function verifyOTP(string $email, string $otp): bool {
        $db = DB::getConnection();
        $stmt = $db->prepare('SELECT * FROM password_resets WHERE email = ? AND otp = ? AND expires_at > NOW()');
        $stmt->execute([$email, $otp]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // Delete used OTP
            $del = $db->prepare('DELETE FROM password_resets WHERE email = ?');
            $del->execute([$email]);
            return true;
        }
        return false;
    }
}
?>
