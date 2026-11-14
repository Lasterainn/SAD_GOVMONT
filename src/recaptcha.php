<?php
namespace App;

class Recaptcha {
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public static function siteKey(): string {
        return getenv('RECAPTCHA_SITE_KEY') ?: '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
    }

    public static function verify(?string $response, ?string $remoteIp = null): bool {
        if (empty($response)) {
            return false;
        }

        $secretKey = getenv('RECAPTCHA_SECRET_KEY') ?: '';
        if ($secretKey === '') {
            return false;
        }

        $postData = http_build_query([
            'secret'   => $secretKey,
            'response' => $response,
            'remoteip' => $remoteIp,
        ]);

        if (function_exists('curl_init')) {
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
            unset($ch);

            if ($result === false) {
                error_log("reCAPTCHA verify error (cURL): " . $error);
                return false;
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n"
                               . "Content-Length: " . strlen($postData) . "\r\n",
                    'content' => $postData,
                    'timeout' => 10,
                ],
            ]);
            $result = @file_get_contents(self::VERIFY_URL, false, $context);
            if ($result === false) {
                error_log("reCAPTCHA verify error (stream): gagal menghubungi Google reCAPTCHA.");
                return false;
            }
        }

        $data = json_decode($result, true);
        return is_array($data) && ($data['success'] ?? false) === true;
    }
}