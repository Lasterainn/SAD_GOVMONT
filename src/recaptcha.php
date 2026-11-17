<?php
namespace App;

class Recaptcha {
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public static function siteKey(): string {
        return trim((string) (getenv('RECAPTCHA_SITE_KEY') ?: ''));
    }

    public static function verify(?string $response, ?string $remoteIp = null): bool {
        if (empty($response)) {
            return false;
        }

        $secretKey = trim((string) (getenv('RECAPTCHA_SECRET_KEY') ?: ''));
        if ($secretKey === '') {
            error_log('reCAPTCHA verify error: RECAPTCHA_SECRET_KEY belum dikonfigurasi.');
            return false;
        }

        $isLocal = (getenv('APP_ENV') ?: 'local') === 'local';
        $hasCaFile = !empty(ini_get('curl.cainfo')) || !empty(ini_get('openssl.cafile'));
        $verifySsl = !($isLocal && !$hasCaFile);

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
                CURLOPT_SSL_VERIFYPEER => $verifySsl,
                CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            ]);
            $result = curl_exec($ch);
            $error  = curl_error($ch);
            unset($ch);

            if ($result === false) {
                error_log('reCAPTCHA verify error (cURL): ' . $error);
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
                'ssl' => [
                    'verify_peer'      => $verifySsl,
                    'verify_peer_name' => $verifySsl,
                ],
            ]);
            $result = @file_get_contents(self::VERIFY_URL, false, $context);
            if ($result === false) {
                error_log('reCAPTCHA verify error (stream): gagal menghubungi Google siteverify.');
                return false;
            }
        }

        $data = json_decode($result, true);
        if (!is_array($data) || ($data['success'] ?? false) !== true) {
            error_log('reCAPTCHA rejected: ' . ($result ?: 'empty response'));
            return false;
        }

        return true;
    }
}