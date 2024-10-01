<?php
session_start();
require '../config.php'; // Include the DB connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get the user's ID from the session
$user_id = $_SESSION['user_id'];

// Get the car ID from the POST request
$car_id = isset($_POST['car_id']) ? intval($_POST['car_id']) : 0;

// Fetch user wallet balance
$stmt = $conn->prepare("SELECT amount FROM wallet WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_wallet = $result->fetch_assoc();
$stmt->close();

// Check if the user has enough balance
if ($user_wallet['amount'] < 10) {
    echo "Insufficient wallet balance!";
    exit();
}

// Deduct $10 from the wallet
$new_balance = $user_wallet['amount'] - 10;
$stmt = $conn->prepare("UPDATE wallet SET amount = ? WHERE user_id = ?");
$stmt->bind_param("di", $new_balance, $user_id);
$stmt->execute();
$stmt->close();

// Fetch car details for the email
$stmt = $conn->prepare("SELECT name, price FROM car WHERE car_id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();
$stmt->close();

// Fetch user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Send confirmation email
$mail = new PHPMailer(true);
try {
    // SMTP configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'emai@gmail.com'; // Your Gmail address
    $mail->Password = 'password'; // Your Gmail password or App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('your_email@gmail.com', 'A.H.G Administrator');
    $mail->addAddress($user['email'], $user['name']); // Use the user's email fetched from the database
    $mail->isHTML(true);
    $mail->Subject = 'Order Confirmation';
    $mail->Body = "<p>Thank you for your order!</p>
                   <p>You have ordered the car: <strong>" . htmlspecialchars($car['name']) . "</strong></p>
                   <p>Price: $" . number_format($car['price'], 2) . "</p>
                   <p>Amount deducted: $10</p>
                   <p>Date of Delivery: " . date('Y-m-d', strtotime('+7 days')) . "</p>
                   <p>Regards,</p><p>A.H.G Administrator</p>";

    $mail->send();

    // Redirect or show success message
    header("Location: dashboard.php"); // Redirect to a success page
    exit();

} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
