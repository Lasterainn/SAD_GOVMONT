<?php
namespace App;

class Env {
    public static function load(string $path): void {
        if (!is_readable($path)) return; // di production bisa lewat env server
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
            [$k, $v] = explode('=', $line, 2);
            $v = trim(preg_replace('/\s+#.*$/', '', $v), " \t\"'");
            putenv(trim($k) . '=' . $v);
        }
    }

    public static function get(string $key): string {
        $v = getenv($key);
        if ($v === false || $v === '') {
            error_log("ENV hilang: {$key}");
            http_response_code(500);
            exit('Konfigurasi sistem tidak lengkap.');
        }
        return $v;
    }

    public static function key(string $key): string {
        $hex = self::get($key);
        if (!preg_match('/^[0-9a-fA-F]{64}$/', $hex)) {
            error_log("{$key} harus 64 karakter hex (32 byte)");
            http_response_code(500);
            exit('Konfigurasi sistem tidak valid.');
        }
        return hex2bin($hex); // 32 byte biner
    }
}