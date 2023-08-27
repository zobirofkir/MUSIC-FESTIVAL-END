<?php
// Include the necessary files

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require '../connection/connection.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

// Create an instance of PHPMailer
$mail = new PHPMailer(true);

// Set up PayPal environment
$clientId = 'AdcVEiTMY2jbLIX1eILOr9aadh5LLt1gjA9jycIEuAhkwD-zXVaMrLr0VZDucVAl3rcbB-fGW7EFJ-wM';
$clientSecret = 'EG4x8uCi5gQ9ORmTWYglwlkp-UBpJHcc53DqFoO9fSrm_9SFiVAXPRqv8JiWmpN4ohMUk_Z-cf_qJBNK';
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);

if (isset($_POST["BuyTicket"])) {
    // Sanitize user inputs
    $TicketName = filter_var($_POST["TicketName"], FILTER_SANITIZE_SPECIAL_CHARS);
    $TicketEmail = filter_var($_POST["TicketEmail"], FILTER_SANITIZE_EMAIL);
    $TicketPhone = filter_var($_POST["TicketPhone"], FILTER_SANITIZE_NUMBER_INT);
    $TicketForm = filter_var($_POST["TicketForm"], FILTER_SANITIZE_SPECIAL_CHARS);
    $TicketFormTwo = filter_var($_POST["TicketFormTwo"], FILTER_SANITIZE_SPECIAL_CHARS);
    $TicketNumber = filter_var($_POST["TicketNumber"], FILTER_SANITIZE_NUMBER_INT);
    $TicketMessage = filter_var($_POST["TicketMessage"], FILTER_SANITIZE_SPECIAL_CHARS);

    // Prepare and execute the database insertion
    $postTicket = $database->prepare("INSERT INTO Ticket (TicketName, TicketEmail, TicketPhone, TicketForm, TicketFormTwo, TicketNumber, TicketMessage) VALUES (:TicketName, :TicketEmail, :TicketPhone, :TicketForm, :TicketFormTwo, :TicketNumber, :TicketMessage)");
    
    $postTicket->bindParam(':TicketName', $TicketName);
    $postTicket->bindParam(':TicketEmail', $TicketEmail);
    $postTicket->bindParam(':TicketPhone', $TicketPhone);
    $postTicket->bindParam(':TicketForm', $TicketForm);
    $postTicket->bindParam(':TicketFormTwo', $TicketFormTwo);
    $postTicket->bindParam(':TicketNumber', $TicketNumber);
    $postTicket->bindParam(':TicketMessage', $TicketMessage);

    if ($postTicket->execute()) {
        // Create an order
        $request = new OrdersCreateRequest();
        $request->headers["prefer"] = "return=representation";
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $_POST["TicketAmount"], // Use the posted value
                    ]
                ]
            ]
        ];

        try {
            $response = $client->execute($request);
            $orderId = $response->result->id;

            // Redirect to PayPal approval URL
            foreach ($response->result->links as $link) {
                if ($link->rel === "approve") {
                    $approvalUrl = $link->href;
                    header("Location: $approvalUrl");
                    exit();
                }
            }
            echo "Unable to create PayPal order.";
        } catch (Exception $e) {
            echo "PayPal error: " . $e->getMessage();
        }
    } else {
        echo "Error ...";
    }
}

// Handle PayPal callback
if (isset($_GET["paypal_token"])) {
    $token = $_GET["paypal_token"]; // Get token from PayPal callback
    // Process the payment and update the database
    // ...
}

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'YOUR_EMAIL@gmail.com'; // Use your email here
    $mail->Password = 'YOUR_EMAIL_PASSWORD'; // Use your email password here
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('YOUR_EMAIL@gmail.com', 'Your Name'); // Use your email and name
    $mail->addAddress($TicketEmail, $TicketName);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Ticket';
    $mail->Body = 'Hello ' . $TicketName . ', You bought this ticket using this email: ' . $TicketEmail;

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>