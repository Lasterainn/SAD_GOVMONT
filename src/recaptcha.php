<?php
namespace App;

class Recaptcha {
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public static function siteKey(): string {
        $config = require __DIR__ . '/../config/config.php';
        return $config['recaptcha']['site_key'];
    }

    public static function verify(?string $response, ?string $remoteIp = null): bool {
        if (empty($response)) {
            return false;
        }

        $config = require __DIR__ . '/../config/config.php';

        $postData = http_build_query([
            'secret'   => $config['recaptcha']['secret_key'],
            'response' => $response,
            'remoteip' => $remoteIp,
        ]);

        $ch = curl_init(self::VERIFY_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result = curl_exec($ch);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($result === false) {
            error_log("reCAPTCHA verify error: " . $error);
            return false; // fail closed
        }

        $data = json_decode($result, true);
        return is_array($data) && ($data['success'] ?? false) === true;
    }
}