<?php
session_start();
require 'config.php'; // Include the DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier']; // This can be either email or phone number
    $password = $_POST['password'];

    // Basic validation
    if (empty($identifier) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Check if user exists in the database by email or phone number
        $stmt = $conn->prepare("SELECT id, username, password, role, verified FROM users WHERE email = ? OR phone_number = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $hashed_password, $role, $verified);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Store user info in session
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                // Redirect based on role
                if ($role == 'admin') {
                    header("Location: admin/dashboard.php");
                } else if ($role == 'user') {
                    // For regular users, check if verified
                    if ($verified == 1) {
                        header("Location: users/dashboard.php");
                    } else {
                        header("Location: email-verification.php");
                    }
                } 
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="image/logo.png" rel="icon">
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <img src="image/logo.png" alt="Logo">
        </div>
        <h2>Welcome Back</h2>
        <form method="POST">
            <input type="text" id="identifier" name="identifier" required placeholder="Email or Phone Number">
            <input type="password" id="password" name="password" required placeholder="Password">
            <button type="submit">Login</button>
            
            <div id="sing">
            <p><a href="recover.php">Forgotten account?</a></p>
                <p><a href="signup.php">Sign up for our store</a></p>
            </div>
        </form>

        <!-- Error message placeholder -->
        <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>
    </div>
</body>

</html>