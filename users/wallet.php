<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
require_once 'config.php';

// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Prepare the SQL query to fetch all wallet amounts for the logged-in user
$query = "SELECT amount FROM wallet WHERE user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Sum all amounts
$totalAmount = 0;
while ($row = $result->fetch_assoc()) {
    $totalAmount += $row['amount']; // Accumulate the total amount
}

// Close the statement and connection
$stmt->close();
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/wallet.css">
    <link href="../image/logo.png" rel="icon" />
        <title>Wallet</title>
</head>

<body>
<header>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="add_car.php">Add my car</a></li>
        <li><a href="wallet.php">Wallet</a></li>
        <li><a href="setting.php">Setting</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</header><br><br>
    <div class="wallet-form-container">
        <h2>Your Wallet Balance</h2>

        <div class="wallet-balance">
            <p>Current Amount: <strong>$<?php echo number_format($totalAmount, 2); ?></strong></p>
        </div><br>

        <div class="form-group">
            <p>To add funds to your wallet, send money via your preferred money app to this phone number: <strong>76976048</strong>.</p>
            <p>The note of the money transfer should include your name and email account.</p>
        </div>
    </div>

</body>

</html>