<?php
namespace App;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/config.php';
            $db = $config['db'];

            // Tambahkan public ke search_path
            $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};options='--search_path={$db['schema']},public'";

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                error_log("PostgreSQL Connection Error: " . $e->getMessage());
                die("Terjadi kesalahan koneksi sistem.");
            }
        }
        return self::$instance;
    }
}