<?php
/**
 * SMSHelper - Sends SMS messages.
 *
 * Uses Twilio SDK if credentials are set; otherwise logs to a file.
 */
class SMSHelper {
    /**
     * Send an SMS message.
     * @param string $to  Destination number in E.164 format.
     * @param string $msg Message body.
     * @return bool       True on success.
     */
    public static function sendSMS(string $to, string $msg): bool {
        $sid   = getenv('TWILIO_SID');
        $token = getenv('TWILIO_TOKEN');
        $from  = getenv('TWILIO_FROM');

        if ($sid && $token && $from) {
            $autoload = __DIR__ . '/../vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
                try {
                    $client = new Twilio\Rest\Client($sid, $token);
                    $client->messages->create($to, ['from' => $from, 'body' => $msg]);
                    return true;
                } catch (Exception $e) {
                    error_log('SMSHelper error: ' . $e->getMessage());
                }
            }
        }
        // Fallback: write to log file
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/sms_fallback_' . date('Ymd') . '.log';
        $entry   = sprintf("[%s] To: %s | Message: %s\n", date('Y-m-d H:i:s'), $to, $msg);
        return (bool)file_put_contents($logFile, $entry, FILE_APPEND);
    }
}
?>
