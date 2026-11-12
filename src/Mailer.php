<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/**
 * Pengirim email. Memakai PHPMailer (composer require phpmailer/phpmailer).
 * MAIL_DRIVER=log hanya berlaku saat APP_DEBUG=1 (OTP ditulis ke error_log, untuk development).
 */
class Mailer {
    public static function sendOtp(string $to, string $name, string $otp, int $ttlMenit): bool {
        $subject = 'Kode Verifikasi JakartaConnect';
        $body = "Halo {$name},\n\n"
              . "Kode verifikasi (OTP) login JakartaConnect Anda:\n\n"
              . "    {$otp}\n\n"
              . "Kode berlaku selama {$ttlMenit} menit dan hanya dapat dipakai satu kali.\n"
              . "JANGAN bagikan kode ini kepada siapa pun, termasuk pihak yang mengaku petugas.\n"
              . "Jika Anda tidak sedang mencoba login, segera ganti password Anda.\n";

        $cfg = (require __DIR__ . '/../config/config.php')['mail'];

        if ($cfg['driver'] === 'log') {
            if (getenv('APP_DEBUG') !== '1') {
                error_log('MAIL_DRIVER=log hanya boleh saat APP_DEBUG=1');
                return false;
            }
            error_log("[DEV-OTP] {$to} => {$otp}");
            return true;
        }

        if (!class_exists(PHPMailer::class)) {
            error_log('PHPMailer belum terpasang (composer require phpmailer/phpmailer)');
            return false;
        }

        try {
            $m = new PHPMailer(true);
            $m->isSMTP();
            $m->Host       = $cfg['host'];
            $m->Port       = $cfg['port'];
            $m->SMTPAuth   = true;
            $m->Username   = $cfg['user'];
            $m->Password   = $cfg['pass'];
            $m->SMTPSecure = $cfg['encryption'] === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $m->CharSet    = 'UTF-8';
            $m->Timeout    = 10;
            $m->setFrom($cfg['from'], $cfg['from_name']);
            $m->addAddress($to, $name);
            $m->isHTML(false);
            $m->Subject = $subject;
            $m->Body    = $body;
            $m->send();
            return true;
        } catch (MailException $e) {
            error_log('Gagal kirim OTP: ' . $e->getMessage()); // jangan pernah log nilai OTP
            return false;
        }
    }
}