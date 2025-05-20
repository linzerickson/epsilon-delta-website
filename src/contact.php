<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Make sure PHPMailer is installed via Composer

// Load environment variables if using phpdotenv (optional)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }
}

// Check if form data is sent via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var(trim($_POST['name'] ?? ''), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $message = filter_var(trim($_POST['message'] ?? ''), FILTER_SANITIZE_STRING);

    if (!$name || !$email || !$message) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields with valid information.']);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // SMTP server settings from environment variables
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME');
        $mail->Password = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getenv('SMTP_PORT') ?: 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress('epsilondeltaanalytics@gmail.com', 'Epsilon Delta Analytics');

        // Content
        $mail->isHTML(false);
        $mail->Subject = 'New Contact Form Submission';
        $mail->Body = "Name: $name\nEmail: $email\n\nMessage:\n$message";

        $mail->send();

        echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
