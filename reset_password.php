<?php
session_start();
require 'config.php';

function validatePassword($password) {
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
        return "Password must be at least 8 characters long, include an uppercase letter, a lowercase letter, and a digit.";
    }
    return true;
}

if (!isset($_SESSION['verified_email'])) {
    header("Location: recover.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['verified_email'];

    $validationResult = validatePassword($new_password);
    if ($validationResult !== true) {
        $error = $validationResult;
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_code_expires = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();
        unset($_SESSION['verified_email'], $_SESSION['reset_email']);
        header("Location: login.php");
        exit;
    }
}
?>
<!-- HTML Form for Reset Password -->
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/reset_password.css">
    <link href="image/logo.png" rel="icon" />
</head>
<body>
    
    <form method="POST">
    <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>
    <h2>Reset Password</h2>
        <input type="password" name="password" required placeholder="New password">
        <input type="password" name="confirm_password" required placeholder="Confirm new password">
        <button type="submit">Reset Password</button>
    </form>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>

