<?php
require_once __DIR__ . "/../config/pengaturan.php";
require_once __DIR__ . "/../libs/PHPMailer/Exception.php";
require_once __DIR__ . "/../libs/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/../libs/PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Kirim email lewat SMTP.
 * Mengembalikan true kalau berhasil, atau string pesan error kalau gagal.
 */
function kirimEmail($tujuan, $subjek, $isiHtml) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($tujuan);

        $mail->isHTML(true);
        $mail->Subject = $subjek;
        $mail->Body = $isiHtml;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Gagal mengirim email: " . $mail->ErrorInfo;
    }
}
