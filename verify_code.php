<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = $_POST['code'];
    $email = $_SESSION['reset_email'];

    $stmt = $conn->prepare("SELECT reset_code, reset_code_expires FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($stored_code, $expires);
    $stmt->fetch();

    if ($stored_code === $entered_code && strtotime($expires) > time()) {
        $_SESSION['verified_email'] = $email;
        header("Location: reset_password.php");
        exit;
    } else {
        $error = "Invalid or expired verification code.";
    }
}
?>
<!-- HTML Form for Code Verification -->
<!DOCTYPE html>
<html>
<head>
    <title>Verify Code</title>
    <link rel="stylesheet" href="css/reset_password.css">
    <link href="image/logo.png" rel="icon" />
</head>
<body>

     
    <form method="POST">
    <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>
    <h2>Verify Code</h2>
        <input type="text" name="code" required placeholder="Enter the verification code">
        <button type="submit">Verify</button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
