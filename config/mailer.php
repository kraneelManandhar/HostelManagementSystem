<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

/**
 * Creates and returns a configured PHPMailer instance using .env variables.
 */
function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);

    // ── SMTP Configuration ──────────────────────────────────────────────────
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST']; 
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD']; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['MAIL_PORT'];

    // ── Sender Info ─────────────────────────────────────────────────────────
    // Using the username and the custom name from your .env
    $mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['MAIL_FROM_NAME']);
    $mail->isHTML(true);

    return $mail;
}