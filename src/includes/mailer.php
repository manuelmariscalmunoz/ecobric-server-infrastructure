<?php
// includes/mailer.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer-6.8.1/src/Exception.php';
require_once __DIR__ . '/PHPMailer-6.8.1/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-6.8.1/src/SMTP.php';

function enviarCorreo($destinatario, $asunto, $cuerpo)
{
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'ecobricsoporte@gmail.com';
        $mail->Password = 'nhwg ypqs kxdo gxfe'; // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Remitente y destinatario
        $mail->setFrom('ecobricsoporte@gmail.com', 'Ecobric Soporte');
        $mail->addAddress($destinatario);

        // Contenido
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;
        $mail->AltBody = strip_tags($cuerpo);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log del error o manejarlo
        error_log("No se pudo enviar el correo. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>