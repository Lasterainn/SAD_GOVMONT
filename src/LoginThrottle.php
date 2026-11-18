<?php
namespace App;

class LoginThrottle {
    private const WINDOW_SECONDS    = 900; // Jendela evaluasi 15 menit
    private const CAPTCHA_THRESHOLD = 3;   // Tampilkan CAPTCHA setelah 3x gagal
    private const MAX_ATTEMPTS_IP   = 10;  // Blokir sementara IP setelah 10x gagal dalam 15 menit
    private const MAX_DELAY_SECONDS = 8;   // Batas maksimum jeda progresif (detik)

    /**
     * Ambil IP klien secara aman (hanya percaya X-Forwarded-For jika berasal dari reverse proxy lokal Caddy/Nginx).
     */
    public static function clientIp(): string {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (in_array($remoteAddr, ['127.0.0.1', '::1'], true) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $candidate = trim($parts[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }
        return filter_var($remoteAddr, FILTER_VALIDATE_IP) ? $remoteAddr : '0.0.0.0';
    }

    public static function failureCount(string $ip): int {
        return count(self::readTimestamps($ip));
    }

    public static function isBlocked(string $ip): bool {
        return self::failureCount($ip) >= self::MAX_ATTEMPTS_IP;
    }

    public static function requiresCaptcha(string $ip): bool {
        return self::failureCount($ip) >= self::CAPTCHA_THRESHOLD;
    }

    /**
     * Terapkan jeda progresif sebelum memproses autentikasi:
     * 0-2 gagal = 0 dtk, 3 gagal = 1 dtk, 4 gagal = 2 dtk, 5 gagal = 4 dtk, >=6 gagal = 8 dtk.
     */
    public static function applyProgressiveDelay(string $ip): void {
        $failures = self::failureCount($ip);
        if ($failures < self::CAPTCHA_THRESHOLD) {
            return;
        }
        $exponent = $failures - self::CAPTCHA_THRESHOLD;
        $delay = min(self::MAX_DELAY_SECONDS, (int)pow(2, $exponent));
        if ($delay > 0) {
            sleep($delay);
        }
    }

    public static function recordFailure(string $ip): int {
        $file = self::storagePath($ip);
        $fp = @fopen($file, 'c+');
        if (!$fp) {
            return 1;
        }

        flock($fp, LOCK_EX);
        $raw = stream_get_contents($fp);
        $now = time();
        $timestamps = self::prune(json_decode($raw ?: '[]', true), $now);
        $timestamps[] = $now;

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($timestamps));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return count($timestamps);
    }

    public static function clear(string $ip): void {
        $file = self::storagePath($ip);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private static function readTimestamps(string $ip): array {
        $file = self::storagePath($ip);
        if (!is_readable($file)) {
            return [];
        }
        $fp = @fopen($file, 'r');
        if (!$fp) {
            return [];
        }
        flock($fp, LOCK_SH);
        $raw = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return self::prune(json_decode($raw ?: '[]', true), time());
    }

    private static function prune($list, int $now): array {
        if (!is_array($list)) {
            return [];
        }
        $cutoff = $now - self::WINDOW_SECONDS;
        return array_values(array_filter($list, static fn($ts) => is_int($ts) && $ts >= $cutoff));
    }

    private static function storagePath(string $ip): string {
        return rtrim(sys_get_temp_dir(), '/\\') . '/jc_throttle_' . hash('sha256', $ip) . '.json';
    }
}