<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include 'config.php'; // Ensure you include your database configuration

// Fetch all cars that are not sold
$query = "SELECT * FROM car WHERE is_sold = 'no'";
$result = $mysqli->query($query);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link href="../image/logo.png" rel="icon" />
    <title>Home Page</title>
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
</header>
<br><br>
<div class="products">
   
    <h2>&nbsp&nbspAvailable Cars</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <div class="car-list">
    <?php while ($carItem = $result->fetch_assoc()): ?>
        <form class="car-item">
            <img src="../uploads/<?php echo htmlspecialchars($carItem['image1']); ?>" alt="<?php echo htmlspecialchars($carItem['name']); ?>" style="width: 200px; height: auto;">
            <h3><?php echo htmlspecialchars($carItem['name']); ?></h3>
            <p>Price: $<?php echo htmlspecialchars($carItem['price']); ?></p>
            <button type="button" onclick="location.href='car_details.php?id=<?php echo $carItem['car_id']; ?>'">More details</button>
        </form>
    <?php endwhile; ?>
</div>

    <?php else: ?>
        <p>No cars available.</p>
    <?php endif; ?>

</div>
</body>
</html>
