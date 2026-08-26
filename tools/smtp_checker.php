<?php
declare(strict_types=1);

/**
 * CAVA LMS - Temporary SMTP Diagnostic Checker
 * -----------------------------------------------
 * PURPOSE : Verify SMTP connection + authentication only. NO email is sent.
 * SCOPE   : Standalone tool. Does NOT load config.php, EmailHelper, or any existing code.
 * REMOVE  : Delete tools/smtp_checker.php once testing is complete.
 * SECURITY: Passwords are never echoed, stored, or logged. Localhost-only access.
 */

// Guard: localhost-only access
$remoteAddr = $_SERVER["REMOTE_ADDR"] ?? "";
$allowedIPs = ["127.0.0.1", "::1", "::ffff:127.0.0.1"];
if (!in_array($remoteAddr, $allowedIPs, true)) {
    http_response_code(403);
    exit("403 Forbidden: This tool is only accessible from localhost.");
}

// Load PHPMailer via project vendor/
$autoload = dirname(__DIR__) . "/vendor/autoload.php";
if (!file_exists($autoload)) {
    die("ERROR: vendor/autoload.php not found. Run composer install first.");
}
require_once $autoload;

use PHPMailer\PHPMailer\SMTP;

// Result status constants
const RESULT_SUCCESS          = "SUCCESS";
const RESULT_AUTH_ERROR       = "AUTH_ERROR";
const RESULT_CONNECTION_ERROR = "CONNECTION_ERROR";
const RESULT_OTHER_ERROR      = "OTHER_ERROR";

/**
 * Tests SMTP connection + authentication only. No email is ever sent.
 */
function check_smtp(string $host, int $port, string $username, string $password, string $secure = "tls"): array
{
    $smtp = new SMTP();
    $smtp->do_debug = SMTP::DEBUG_OFF;

    try {
        $timeout = 10;
        $connectHost = (strtolower($secure) === "ssl") ? "ssl://" . $host : $host;
        $connected = $smtp->connect($connectHost, $port, $timeout);

        if (!$connected) {
            return [
                "status"  => RESULT_CONNECTION_ERROR,
                "message" => "Cannot reach {$host}:{$port}. Check the host, port, and that SMTP is not blocked by a firewall or your network.",
            ];
        }

        if (!$smtp->hello(gethostname() ?: "localhost")) {
            $smtp->quit();
            return [
                "status"  => RESULT_CONNECTION_ERROR,
                "message" => "EHLO/HELO handshake failed. The server did not respond correctly.",
            ];
        }

        if (strtolower($secure) === "tls") {
            if (!$smtp->startTLS()) {
                $smtp->quit();
                return [
                    "status"  => RESULT_CONNECTION_ERROR,
                    "message" => "STARTTLS negotiation failed. The server may not support TLS on port {$port}.",
                ];
            }
            $smtp->hello(gethostname() ?: "localhost");
        }

        $authResult = $smtp->authenticate($username, $password);
        $smtp->quit();

        if ($authResult) {
            return [
                "status"  => RESULT_SUCCESS,
                "message" => "Authentication successful. SMTP credentials are valid for {$host}.",
            ];
        }

        return [
            "status"  => RESULT_AUTH_ERROR,
            "message" => "Authentication failed. Verify the email address and that you are using an app-specific password (not the account login password). Also ensure 2FA is enabled and SMTP is allowed in Zoho account settings.",
        ];

    } catch (\Exception $e) {
        $safeMsg = preg_replace("/password[^\s,]*/i", "[REDACTED]", $e->getMessage());
        return [
            "status"  => RESULT_OTHER_ERROR,
            "message" => "Unexpected error: " . htmlspecialchars($safeMsg ?? "", ENT_QUOTES, "UTF-8"),
        ];
    }
}

// Handle form submission
$result   = null;
$formData = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $formData["host"]     = trim($_POST["smtp_host"] ?? "smtp.zoho.com");
    $formData["port"]     = (int) ($_POST["smtp_port"] ?? 587);
    $formData["username"] = trim($_POST["smtp_user"] ?? "");
    $formData["secure"]   = in_array($_POST["smtp_secure"] ?? "", ["tls", "ssl"]) ? $_POST["smtp_secure"] : "tls";
    $password             = $_POST["smtp_pass"] ?? "";

    if (empty($formData["username"])) {
        $result = ["status" => RESULT_OTHER_ERROR, "message" => "Email/username cannot be empty."];
    } elseif (empty($password)) {
        $result = ["status" => RESULT_OTHER_ERROR, "message" => "Password cannot be empty."];
    } elseif (empty($formData["host"])) {
        $result = ["status" => RESULT_OTHER_ERROR, "message" => "SMTP host cannot be empty."];
    } elseif ($formData["port"] < 1 || $formData["port"] > 65535) {
        $result = ["status" => RESULT_OTHER_ERROR, "message" => "Invalid port number."];
    } else {
        $result = check_smtp($formData["host"], $formData["port"], $formData["username"], $password, $formData["secure"]);
    }

    $password = "";
    unset($password);
}

function badge_class(string $status): string {
    return match ($status) {
        RESULT_SUCCESS          => "badge-success",
        RESULT_AUTH_ERROR       => "badge-auth",
        RESULT_CONNECTION_ERROR => "badge-conn",
        default                 => "badge-other",
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Diagnostic Checker - CAVA LMS (Temp Tool)</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0f1117;
            --surface:   #1a1d27;
            --border:    #2a2d3e;
            --accent:    #6c63ff;
            --accent-h:  #8b85ff;
            --text:      #e2e8f0;
            --muted:     #94a3b8;
            --success:   #22c55e;
            --warn:      #f59e0b;
            --danger:    #ef4444;
            --info:      #38bdf8;
            --radius:    12px;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 16px 80px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 36px 40px;
            width: 100%;
            max-width: 560px;
            box-shadow: 0 8px 40px rgba(0,0,0,.45);
        }

        .header { margin-bottom: 28px; }
        .header h1 { font-size: 1.4rem; font-weight: 700; color: var(--text); }
        .header p  { margin-top: 6px; font-size: .85rem; color: var(--muted); line-height: 1.5; }

        .warning-banner {
            background: rgba(245, 158, 11, .12);
            border: 1px solid rgba(245, 158, 11, .35);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .8rem;
            color: var(--warn);
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .warning-banner svg { flex-shrink: 0; margin-top: 1px; }

        label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 6px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .form-group { margin-bottom: 18px; }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        select {
            width: 100%;
            background: #0f1117;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            padding: 10px 14px;
            font-size: .9rem;
            outline: none;
            transition: border-color .2s;
        }
        input:focus, select:focus { border-color: var(--accent); }

        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .port-secure { display: grid; grid-template-columns: 100px 1fr; gap: 12px; align-items: end; }

        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 44px; }
        .toggle-pass {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--muted); padding: 4px; line-height: 0;
        }
        .toggle-pass:hover { color: var(--text); }

        .hint {
            font-size: .76rem;
            color: var(--muted);
            margin-top: 5px;
        }

        .presets { margin-bottom: 20px; }
        .presets label { margin-bottom: 8px; }
        .preset-btns { display: flex; gap: 8px; flex-wrap: wrap; }
        .preset-btn {
            background: #0f1117;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--muted);
            font-size: .78rem;
            padding: 6px 12px;
            cursor: pointer;
            transition: border-color .2s, color .2s;
        }
        .preset-btn:hover { border-color: var(--accent); color: var(--text); }

        .btn-check {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s, transform .1s;
            margin-top: 8px;
        }
        .btn-check:hover  { background: var(--accent-h); }
        .btn-check:active { transform: scale(.98); }
        .btn-check:disabled { opacity: .5; cursor: not-allowed; }

        /* Result box */
        .result {
            margin-top: 24px;
            border-radius: 8px;
            padding: 16px 18px;
            font-size: .88rem;
            line-height: 1.6;
            border-width: 1px;
            border-style: solid;
        }
        .result-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: .95rem;
        }

        /* Status colour variants */
        .result.badge-success { background: rgba(34,197,94,.1);  border-color: rgba(34,197,94,.35);  }
        .result.badge-auth    { background: rgba(239,68,68,.1);  border-color: rgba(239,68,68,.35);  }
        .result.badge-conn    { background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.35); }
        .result.badge-other   { background: rgba(56,189,248,.1); border-color: rgba(56,189,248,.35); }

        .badge-success .result-header { color: var(--success); }
        .badge-auth    .result-header { color: var(--danger);  }
        .badge-conn    .result-header { color: var(--warn);    }
        .badge-other   .result-header { color: var(--info);    }

        .result .detail { color: var(--muted); }

        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 28px 0 20px;
        }

        .footer-note {
            font-size: .75rem;
            color: #475569;
            text-align: center;
            margin-top: 24px;
        }

        /* Spinner */
        .spinner { display: none; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h1>⚡ SMTP Diagnostic Checker</h1>
        <p>Tests SMTP connection &amp; authentication only. <strong>No email is ever sent.</strong><br>
           Passwords are never stored or logged.</p>
    </div>

    <div class="warning-banner">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        <span><strong>Temporary tool</strong> — accessible from localhost only. Delete <code>tools/smtp_checker.php</code> after testing.</span>
    </div>

    <!-- Quick presets -->
    <div class="presets">
        <label>Quick Presets</label>
        <div class="preset-btns">
            <button type="button" class="preset-btn" onclick="applyPreset('smtp.zoho.com', 587, 'tls')">Zoho (TLS/587)</button>
            <button type="button" class="preset-btn" onclick="applyPreset('smtppro.zoho.com', 465, 'ssl')">Zoho Pro (SSL/465)</button>
            <button type="button" class="preset-btn" onclick="applyPreset('smtp.gmail.com', 587, 'tls')">Gmail (TLS/587)</button>
            <button type="button" class="preset-btn" onclick="applyPreset('smtp.gmail.com', 465, 'ssl')">Gmail (SSL/465)</button>
        </div>
    </div>

    <form method="POST" id="checker-form" autocomplete="off">

        <div class="form-group">
            <label for="smtp_host">SMTP Host</label>
            <input type="text" id="smtp_host" name="smtp_host"
                   value="<?= htmlspecialchars($formData['host'] ?? 'smtp.zoho.com', ENT_QUOTES) ?>"
                   placeholder="smtp.zoho.com" required>
        </div>

        <div class="port-secure">
            <div class="form-group">
                <label for="smtp_port">Port</label>
                <input type="number" id="smtp_port" name="smtp_port"
                       value="<?= htmlspecialchars((string)($formData['port'] ?? 587), ENT_QUOTES) ?>"
                       min="1" max="65535" required>
            </div>
            <div class="form-group">
                <label for="smtp_secure">Encryption</label>
                <select id="smtp_secure" name="smtp_secure">
                    <option value="tls" <?= ($formData['secure'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>STARTTLS (port 587)</option>
                    <option value="ssl" <?= ($formData['secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL/TLS (port 465)</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="smtp_user">Client Zoho Email (Username)</label>
            <input type="email" id="smtp_user" name="smtp_user"
                   value="<?= htmlspecialchars($formData['username'] ?? '', ENT_QUOTES) ?>"
                   placeholder="client@domain.com" autocomplete="off" required>
        </div>

        <div class="form-group">
            <label for="smtp_pass">App-Specific Password</label>
            <div class="password-wrap">
                <input type="password" id="smtp_pass" name="smtp_pass"
                       placeholder="Zoho app password (not account password)"
                       autocomplete="new-password" required>
                <button type="button" class="toggle-pass" onclick="togglePassword()" title="Show/hide password">
                    <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
            <p class="hint">Generate an app-specific password in Zoho Account → Security → App Passwords (requires 2FA enabled).</p>
        </div>

        <button type="submit" class="btn-check" id="submit-btn">
            <span id="btn-label">Run SMTP Check</span>
            <div class="spinner" id="spinner"></div>
        </button>
    </form>

    <?php if ($result !== null): ?>
    <hr class="divider">
    <div class="result <?= badge_class($result['status']) ?>">
        <div class="result-header">
            <?php
            $icon = match ($result['status']) {
                RESULT_SUCCESS          => '✓',
                RESULT_AUTH_ERROR       => '✕',
                RESULT_CONNECTION_ERROR => '⚠',
                default                 => 'ℹ',
            };
            echo $icon . ' ' . htmlspecialchars($result['status'], ENT_QUOTES);
            ?>
        </div>
        <p class="detail"><?= htmlspecialchars($result['message'], ENT_QUOTES) ?></p>

        <?php if ($result['status'] === RESULT_AUTH_ERROR): ?>
        <p class="detail" style="margin-top: 10px; font-size:.8rem;">
            <strong>Common causes:</strong> incorrect app-specific password, app passwords not enabled on the account,
            IMAP/SMTP access not enabled in Zoho settings, or account 2FA is off (required for app passwords).
        </p>
        <?php elseif ($result['status'] === RESULT_CONNECTION_ERROR): ?>
        <p class="detail" style="margin-top: 10px; font-size:.8rem;">
            <strong>Common causes:</strong> wrong host/port combination, firewall blocking port <?= (int)($formData['port'] ?? 587) ?>,
            or SMTP disabled on the Zoho account.
        </p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <p class="footer-note">CAVA LMS · Temporary diagnostic utility · Not part of production build</p>
</div>

<script>
// Auto-sync port when encryption changes
document.getElementById('smtp_secure').addEventListener('change', function () {
    const portInput = document.getElementById('smtp_port');
    if (this.value === 'ssl' && portInput.value === '587') portInput.value = '465';
    if (this.value === 'tls' && portInput.value === '465') portInput.value = '587';
});

// Auto-sync encryption when port changes
document.getElementById('smtp_port').addEventListener('change', function () {
    const sel = document.getElementById('smtp_secure');
    if (this.value === '465') sel.value = 'ssl';
    if (this.value === '587') sel.value = 'tls';
});

function applyPreset(host, port, secure) {
    document.getElementById('smtp_host').value   = host;
    document.getElementById('smtp_port').value   = port;
    document.getElementById('smtp_secure').value = secure;
}

function togglePassword() {
    const input = document.getElementById('smtp_pass');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

// Show spinner during submission
document.getElementById('checker-form').addEventListener('submit', function () {
    document.getElementById('btn-label').style.display = 'none';
    document.getElementById('spinner').style.display   = 'inline-block';
    document.getElementById('submit-btn').disabled = true;
});
</script>
</body>
</html>
