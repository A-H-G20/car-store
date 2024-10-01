<?php
session_start();
require 'config.php'; // DB connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email exists
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name);
        $stmt->fetch();

        // Generate verification code
        $verification_code = rand(100000, 999999);
        $expiry_time = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Save the code to the database
        $updateStmt = $conn->prepare("UPDATE users SET reset_code = ?, reset_code_expires = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $verification_code, $expiry_time, $user_id);
        $updateStmt->execute();

        // Send the email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'email@gmail.com'; // Your Gmail address
            $mail->Password = 'password'; // Your Gmail password or App PasswordLS;
            $mail->Port = 587;

            $mail->setFrom('your_email@gmail.com', 'Administrator');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification';
            $mail->Body = "Dear $name, <br>Your verification code is: <b>$verification_code</b>.<br>This code will expire in 1 hour.";

            $mail->send();
            $_SESSION['reset_email'] = $email;
            header("Location: verify_code.php");
            exit;
        } catch (Exception $e) {
            $error = "Failed to send email. Please try again.";
        }
    } else {
        $error = "Email not found!";
    }
}
?>
<!-- HTML Form for Forgot Password -->
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/reset_password.css">
    <link href="image/logo.png" rel="icon" />
</head>
<body>
   
    <form method="POST">
    <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>
    <h2>Forgot Password</h2>
        <input type="email" name="email" required placeholder="Enter your email">
        <button type="submit">Send Verification Code</button>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </form>
   
</body>
</html>
