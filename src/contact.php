<?php
// Load PHPMailer classes
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env file
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    $emailPassword = $env['EMAIL_PASSWORD'] ?? null;
} else {
    die('Environment file not found.');
}

// Make sure form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize form fields
    $name    = trim($_POST["name"] ?? '');
    $email   = trim($_POST["email"] ?? '');
    $subject = trim($_POST["subject"] ?? '');
    $message = trim($_POST["message"] ?? '');

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'epsilondeltaanalytics@gmail.com'; // ✅ Gmail address
        $mail->Password = $emailPassword;         // ✅ App Password (not real password)
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress('epsilondeltaanalytics@gmail.com');

        // Content
        $mail->isHTML(true);
        $mail->Subject = !empty($subject) ? $subject : 'New Message From Website!';
        $mail->Body    = "
            <h3>Contact Form Message</h3>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Message:</strong><br>" . nl2br($message) . "</p>
        ";

        // Try to send email
        if (!$mail->send()) {
            header("Location: ../contact.html?status=error");
            exit();
        } else {
            header("Location: ../contact.html?status=success");
            exit();
        }

    } catch (Exception $e) {
        // Log error if needed, then redirect
        error_log('Mail error: ' . $mail->ErrorInfo);
        header("Location: ../contact.html?status=error");
        exit();
    }

} else {
    // If not a POST request, block access
    header("Location: ../contact.html");
    exit();
}
