<?php
// includes/notify.php
// Helper notifikasi email. Menggunakan PHPMailer jika tersedia, fallback ke mail() PHP.

function send_email(string $to, string $subject, string $body): bool {
    // Coba PHPMailer (jika terinstall via composer)
    $phpmailer = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($phpmailer)) {
        require_once $phpmailer;
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                // Konfigurasi SMTP - sesuaikan dengan provider email Anda
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'EMAIL_ANDA@gmail.com';
                $mail->Password   = 'APP_PASSWORD_GMAIL';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';
                $mail->setFrom('noreply@elapor.id', 'E-Lapor Masyarakat');
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->isHTML(true);
                return $mail->send();
            } catch (\Exception $e) {
                error_log("PHPMailer error: " . $e->getMessage());
                return false;
            }
        }
    }
    // Fallback ke mail() bawaan PHP (sering diblokir di hosting gratis)
    $headers = "From: noreply@elapor.id\r\nContent-Type: text/plain; charset=UTF-8";
    return @mail($to, $subject, $body, $headers);
}

function notify_status_changed(string $email, string $nama, int $ticketId, string $judul, string $status, string $prioritas): void {
    $subject = "Update Pengaduan #$ticketId - $status";
    $body = "Halo $nama,\n\nPengaduan Anda berjudul \"$judul\" telah diperbarui.\n\nStatus: $status\nPrioritas: $prioritas\n\nSilakan login ke E-Lapor untuk melihat detail dan komentar admin.\n\nSalam,\nTim E-Lapor Masyarakat";
    send_email($email, $subject, $body);
}