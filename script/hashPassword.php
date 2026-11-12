<?php
// Pemakaian (CLI):  php tools/hash_password.php
// Menghasilkan hash argon2id (parameter sama dengan Auth::registerWarga) untuk INSERT manual di pgAdmin.
// Password dibaca dari stdin agar tidak tersimpan di history shell.

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../src/Validator.php';

fwrite(STDOUT, "Password: ");
@system('stty -echo 2>/dev/null');
$pwd = rtrim((string)fgets(STDIN), "\r\n");
@system('stty echo 2>/dev/null');
fwrite(STDOUT, "\n");

$errors = \App\Validator::validatePassword($pwd);
if ($errors) {
  fwrite(STDERR, "Password tidak memenuhi kebijakan:\n- " . implode("\n- ", $errors) . "\n");
  exit(1);
}

echo password_hash($pwd, PASSWORD_ARGON2ID, [
  'memory_cost' => 65536,
  'time_cost'   => 4,
  'threads'     => 1,
]) . PHP_EOL;