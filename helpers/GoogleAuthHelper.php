<?php
// helpers/GoogleAuthHelper.php
// Minimal Google OAuth helper without external library (fallback)

class GoogleAuthHelper {
    public static function getAuthUrl() {
        $clientId = GOOGLE_CLIENT_ID;
        $redirectUri = GOOGLE_REDIRECT_URI;
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth2state'] = $state;
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $state,
            'prompt' => 'select_account'
        ];
        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        return $url;
    }

    public static function getAccessToken($code) {
        $clientId = GOOGLE_CLIENT_ID;
        $clientSecret = GOOGLE_CLIENT_SECRET;
        $redirectUri = GOOGLE_REDIRECT_URI;
        $postFields = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($postFields),
                'timeout' => 30
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents('https://oauth2.googleapis.com/token', false, $context);
        if ($result === false) return null;
        $data = json_decode($result, true);
        return $data['access_token'] ?? null;
    }

    public static function getUserInfo($accessToken) {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer $accessToken\r\n"
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, $context);
        if ($result === false) return null;
        return json_decode($result, true);
    }
}
?>
