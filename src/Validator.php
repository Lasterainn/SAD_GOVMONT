<?php
namespace App;

class Validator {
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePassword(string $password): array {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = "Password minimal 8 karakter.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password harus mengandung minimal 1 huruf kapital.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password harus mengandung minimal 1 huruf kecil.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password harus mengandung minimal 1 angka.";
        }
        if (!preg_match('/[\W_]/', $password)) {
            $errors[] = "Password harus mengandung minimal 1 simbol.";
        }

        $weakPasswords = ['password123', 'password12345', '12345678', 'admin12345', 'jakarta123'];
        if (in_array(strtolower($password), $weakPasswords)) {
            $errors[] = "Password terlalu umum / lemah.";
        }

        return $errors;
    }

    public static function validateNIK(string $nik): bool {
        return preg_match('/^[0-9]{16}$/', $nik) === 1;
    }
}