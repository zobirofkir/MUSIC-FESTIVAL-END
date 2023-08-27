<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require '../vendor/autoload.php';

// Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);
include '../connection/connection.php';

if (isset($_POST['contact'])) {
    $username = filter_var($_POST["username"], FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $company = filter_var($_POST["company"], FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_var($_POST["message"], FILTER_SANITIZE_SPECIAL_CHARS);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format";
        exit;
    }

    $PostContact = $database->prepare("INSERT INTO Contact(username, email, company, message) VALUES (:username, :email, :company, :message)");
    $PostContact->bindParam(':username', $username);
    $PostContact->bindParam(':email', $email);
    $PostContact->bindParam(':company', $company);
    $PostContact->bindParam(':message', $message);

    try {
        if ($PostContact->execute()) {
            // Server settings
            $mail->isSMTP();                      // Send using SMTP
            $mail->Host = 'smtp.gmail.com';       // Set the SMTP server to send through
            $mail->SMTPAuth = true;               // Enable SMTP authentication
            $mail->Username = 'zobirofkir30@gmail.com';  // SMTP username
            $mail->Password = 'oeednpphsfesfdin';        // SMTP password
            $mail->SMTPSecure = 'tls';            // Enable TLS encryption
            $mail->Port = 587;                    // TCP port to connect to

            $SendEmailName = filter_var($username, FILTER_SANITIZE_SPECIAL_CHARS);
            $SendEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

            $mail->setFrom('zobirofkir30@gmail.com', 'Zobir');
            $mail->addAddress($SendEmail, $SendEmailName);

            $mail->isHTML(true);
            $mail->Subject = 'Hello ' . $SendEmailName;
            $mail->Body = 'Hello ' . $SendEmailName . ', We will contact you soon at this email ' . $SendEmail;

            $mail->send();
            echo "Thank you! We will get in touch soon.";
        } else {
            echo "Database error: Unable to insert contact information.";
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Email error: " . $e->getMessage();
    }
}
?>
