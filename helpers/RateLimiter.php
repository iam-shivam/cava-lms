<?php
/**
 * RateLimiter - Helper for rate limiting OTP, logins, and registrations.
 * Uses the `otp_requests` and `login_attempts` tables.
 */
class RateLimiter {
    // Configurable thresholds
    const OTP_MAX_PER_HOUR = 5;
    const OTP_MAX_PER_HOUR_IP = 20;
    const OTP_COOLDOWN_SECONDS = 60;
    const OTP_MAX_INVALID = 5;
    const OTP_LOCK_MINUTES = 15;

    const LOGIN_MAX_INVALID = 5;
    const LOGIN_LOCK_MINUTES = 15;
    const LOGIN_MAX_PER_HOUR_IP = 100;
    const REGISTRATION_MAX_PER_HOUR_IP = 100;

    /**
     * Get client IP address.
     */
    public static function getIP(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Check if OTP request is allowed.
     */
    public static function canRequestOTP(string $identifier, string $ip, ?string &$reason = null): bool {
        $db = DB::getConnection();

        // Account‑wide lock: check failed OTP verification attempts for both email and mobile
        if (!self::canAttemptOTPVerification($identifier, $ip, $reason)) {
            return false;
        }

        // 3. Hourly limit per identifier
        $stmt = $db->prepare('SELECT COUNT(*) FROM otp_requests WHERE identifier = ? AND requested_at >= (NOW() - INTERVAL 1 HOUR)');
        $stmt->execute([$identifier]);
        if ($stmt->fetchColumn() >= self::OTP_MAX_PER_HOUR) {
            $reason = 'You have exceeded the maximum of ' . self::OTP_MAX_PER_HOUR . ' OTP requests per hour for this email/mobile.';
            return false;
        }

        // 4. Hourly limit per IP
        $stmt = $db->prepare('SELECT COUNT(*) FROM otp_requests WHERE ip_address = ? AND requested_at >= (NOW() - INTERVAL 1 HOUR)');
        $stmt->execute([$ip]);
        if ($stmt->fetchColumn() >= self::OTP_MAX_PER_HOUR_IP) {
            $reason = 'Too many OTP requests from this connection. Please try again later.';
            return false;
        }

        // 5. Cooldown check (60 seconds)
        $stmt = $db->prepare('SELECT MAX(requested_at) FROM otp_requests WHERE identifier = ?');
        $stmt->execute([$identifier]);
        $last = $stmt->fetchColumn();
        if ($last) {
            $diff = time() - strtotime($last);
            if ($diff < self::OTP_COOLDOWN_SECONDS) {
                $reason = 'Please wait ' . (self::OTP_COOLDOWN_SECONDS - $diff) . ' seconds before requesting another OTP.';
                return false;
            }
        }

        return true;
    }

    /**
     * Check if OTP verification is allowed for an account, regardless of whether the identifier is email or mobile.
     * This checks login_attempts for both the provided identifier and the linked email (or mobile) of the same user.
     */
    public static function canAttemptOTPVerification(string $identifier, string $ip, ?string &$reason = null): bool {
        $db = DB::getConnection();
        // Resolve the primary email for the identifier (if identifier is mobile, get email)
        $email = '';
        $stmt = $db->prepare('SELECT email FROM users WHERE mobile_number = ?');
        $stmt->execute([$identifier]);
        $email = $stmt->fetchColumn();
        if (!$email) {
            // identifier might already be email
            $email = $identifier;
        }
        // Count failed OTP verification attempts for both identifiers within lock window
        $stmt = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE (identifier = ? OR identifier = ?) AND attempt_type = "otp_verify" AND success = 0 AND attempted_at >= (NOW() - INTERVAL ' . self::OTP_LOCK_MINUTES . ' MINUTE)');
        $stmt->execute([$identifier, $email]);
        if ($stmt->fetchColumn() >= self::OTP_MAX_INVALID) {
            $reason = "Too many failed verification attempts. Please try again after " . self::OTP_LOCK_MINUTES . " minutes.";
            return false;
        }
        return true;
    }

    /**
     * Check if OTP verification is allowed.
     */
    public static function canVerifyOTP(string $identifier, string $ip, ?string &$reason = null): bool {
        // Account‑wide lock: check failed OTP verification attempts for both email and mobile
        return self::canAttemptOTPVerification($identifier, $ip, $reason);
    }




    /**
     * Check if login attempt is allowed.
     */
    public static function canLogin(string $identifier, string $ip, ?string &$reason = null): bool {
        $db = DB::getConnection();

        // 1. Password login failure lock
        $stmt = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE identifier = ? AND attempt_type = "login" AND success = 0 AND attempted_at >= (NOW() - INTERVAL ' . self::LOGIN_LOCK_MINUTES . ' MINUTE)');
        $stmt->execute([$identifier]);
        if ($stmt->fetchColumn() >= self::LOGIN_MAX_INVALID) {
            $reason = 'Too many failed login attempts. This account is temporarily locked for ' . self::LOGIN_LOCK_MINUTES . ' minutes.';
            return false;
        }

        // 2. Hourly limit per IP
        $stmt = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_type = "login" AND attempted_at >= (NOW() - INTERVAL 1 HOUR)');
        $stmt->execute([$ip]);
        if ($stmt->fetchColumn() >= self::LOGIN_MAX_PER_HOUR_IP) {
            $reason = 'Too many login attempts from this connection. Please try again later.';
            return false;
        }

        return true;
    }

    /**
     * Check if registration is allowed.
     */
    public static function canRegister(string $ip, ?string &$reason = null): bool {
        $db = DB::getConnection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_type = "registration" AND attempted_at >= (NOW() - INTERVAL 1 HOUR)');
        $stmt->execute([$ip]);
        if ($stmt->fetchColumn() >= self::REGISTRATION_MAX_PER_HOUR_IP) {
            $reason = 'Too many registration requests from this connection. Please try again later.';
            return false;
        }
        return true;
    }

    public static function logOTPRequest(string $identifier, string $ip): void {
        $db = DB::getConnection();
        $stmt = $db->prepare('INSERT INTO otp_requests (identifier, ip_address) VALUES (?, ?)');
        $stmt->execute([$identifier, $ip]);
    }

    public static function logOTPVerify(string $identifier, string $ip, bool $success): void {
        $db = DB::getConnection();
        $stmt = $db->prepare('INSERT INTO login_attempts (identifier, ip_address, attempt_type, success) VALUES (?, ?, "otp_verify", ?)');
        $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
    }

    public static function logLogin(string $identifier, string $ip, bool $success): void {
        $db = DB::getConnection();
        $stmt = $db->prepare('INSERT INTO login_attempts (identifier, ip_address, attempt_type, success) VALUES (?, ?, "login", ?)');
        $stmt->execute([$identifier, $ip, $success ? 1 : 0]);
    }

    public static function logRegistration(string $ip): void {
        $db = DB::getConnection();
        $stmt = $db->prepare('INSERT INTO login_attempts (identifier, ip_address, attempt_type, success) VALUES ("", ?, "registration", 1)');
        $stmt->execute([$ip]);
    }
}
?>
