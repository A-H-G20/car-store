<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
require_once 'config.php';

// Get car ID from the URL
$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch car details along with the owner's phone number from the database
$sql = "SELECT car.*, owner.phone_number AS owner_phone 
        FROM car 
        JOIN users AS owner ON car.user_id = user_id 
        WHERE car.car_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if car exists
if ($result->num_rows === 0) {
    echo "Car not found.";
    exit();
}

$car = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/car_details.css">
    <title><?php echo htmlspecialchars($car['name']); ?> Details</title>
</head>

<body>
    <div class="container">
        <!-- Back Button Image -->
        <div class="back-button">
            <a href="dashboard.php">
                <img src="../image/back.png" alt="Go Back">
            </a>
        </div>
        <br>
        <!-- Image Slider -->
        <div class="slider">
            <div class="slides">
                <?php for ($i = 1; $i <= 8; $i++): ?>
                    <?php if (!empty($car['image' . $i])): ?>
                        <div class="slide">
                            <img src="../uploads/<?php echo htmlspecialchars($car['image' . $i]); ?>" alt="Car Image <?php echo $i; ?>">
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <button class="prev" onclick="moveSlide(-1)">&#10094;</button>
            <button class="next" onclick="moveSlide(1)">&#10095;</button>
        </div>

        <!-- Car Information -->
        <div class="car-info">
            <h2><?php echo htmlspecialchars($car['name']); ?></h2>
            <p class="price">Price: $<?php echo number_format($car['price'], 2); ?></p>
            <p><strong>Model:</strong> <?php echo htmlspecialchars($car['model']); ?></p>
            <p><strong>Brand:</strong> <?php echo htmlspecialchars($car['brand']); ?></p>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
            <br><br>
        </div>
                         <!-- Order Button -->
        <form action="order_process.php" method="POST">
            <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
            <button type="submit">Order</button>
        </form>
        <!-- Contact Owner Button -->
        <button>
            <a href="https://wa.me/<?php echo urlencode($car['owner_phone']); ?>?text=<?php 
                echo urlencode("Hi, I am interested in the car " . $car['name'] . ".\nHere are the details:\n\n" . 
                               "Model: " . $car['model'] . "\n" . 
                               "Brand: " . $car['brand'] . "\n" . 
                               "Price: $" . number_format($car['price'], 2) . "\n" . 
                               "Description: " . strip_tags($car['description']) . "\n\nCan I have more info?");
            ?>" target="_blank">
                Contact Owner
            </a>
        </button>

       
    </div>

    <script>
        let currentIndex = 0;
        const slides = document.querySelectorAll('.slide');

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.style.display = (i === index) ? 'block' : 'none';
            });
        }

        function moveSlide(step) {
            currentIndex += step;
            if (currentIndex >= slides.length) currentIndex = 0;
            if (currentIndex < 0) currentIndex = slides.length - 1;
            showSlide(currentIndex);
        }

        // Initialize slider
        showSlide(currentIndex);
    </script>
</body>

</html>
