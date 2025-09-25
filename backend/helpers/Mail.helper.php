<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

class MailHelper
{
    private static $lastError = '';

    public static function getLastError(): string
    {
        return (string)self::$lastError;
    }

    public static function send($toEmail, $toName, $subject, $htmlBody): bool
    {
        global $smtp;
        $cfg = (isset($smtp) && is_array($smtp)) ? $smtp : null;
        if (!$cfg) { return false; }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $cfg['host'] ?? '';
            $mail->SMTPAuth = true;
            $mail->Username = isset($cfg['username']) ? trim($cfg['username']) : '';
            $mail->Password = isset($cfg['password']) ? trim($cfg['password']) : '';
            $enc = strtolower($cfg['encryption'] ?? 'tls');
            if ($enc === 'ssl' || $enc === 'smtps') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // port 465
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // port 587
                $mail->SMTPAutoTLS = true;
            }
            $mail->Port = isset($cfg['port']) ? (int)$cfg['port'] : ($enc === 'ssl' ? 465 : 587);

            $fromEmail = $cfg['from_email'] ?? 'no-reply@example.com';
            $fromName = $cfg['from_name'] ?? 'BBA Reviews';
            $mail->setFrom($fromEmail, $fromName);
            if (!empty($toEmail)) {
                $mail->addAddress($toEmail, $toName ?: $toEmail);
            }
            $mail->isHTML(true);
            $mail->Subject = $subject ?? '';
            $mail->Body = $htmlBody ?? '';

            if (empty($mail->Host) || empty($mail->Username)) {
                return false;
            }

            // Optional: allow self-signed for local dev if configured
            if (!empty($cfg['allow_self_signed'])) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            // Optional debug
            if (isset($cfg['debug']) && (int)$cfg['debug'] > 0) {
                $mail->SMTPDebug = (int)$cfg['debug'];
                $mail->Debugoutput = function ($str, $level) {
                    error_log("[SMTP][L$level] " . $str);
                };
            }

            $ok = $mail->send();
            if (!$ok) {
                self::$lastError = $mail->ErrorInfo ?: 'unknown error';
            } else {
                self::$lastError = '';
            }
            return $ok;
        } catch (Exception $e) {
            error_log('[MAIL] send failed: ' . $e->getMessage());
            self::$lastError = $e->getMessage();
            return false;
        }
    }
}


