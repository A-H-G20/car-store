<?php
session_start(); // Start session to access user data
require 'config.php'; // Include database configuration

// Process form submission for editing car data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_car'])) {
    $car_id = $_POST['car_id'];
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $is_sold = $_POST['is_sold']; // Correctly get the "is_sold" value from the form

    // Process image updates
    $imageFields = ['image1', 'image2', 'image3', 'image4', 'image5', 'image6', 'image7', 'image8'];
    $images = array_fill(0, 8, null);

    for ($i = 1; $i <= 8; $i++) {
        if (!empty($_FILES["image$i"]["name"])) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["image$i"]["name"]);
            if (move_uploaded_file($_FILES["image$i"]["tmp_name"], $target_file)) {
                $images[$i - 1] = $target_file;
            }
        }
    }

    // Build the update query
    $updateFields = "name = '$name', brand = '$brand', model = '$model', price = '$price', description = '$description', is_sold = '$is_sold'";
    for ($i = 1; $i <= 8; $i++) {
        if ($images[$i - 1] !== null) {
            $updateFields .= ", image$i = '{$images[$i - 1]}'";
        }
    }

    $updateQuery = "UPDATE car SET $updateFields WHERE car_id = $car_id";
    if ($mysqli->query($updateQuery)) {
        header("Location: add_car.php"); // Redirect after successful edit
        exit();
    } else {
        echo "Error updating car: " . $mysqli->error;
    }
}

// Handle car deletion
if (isset($_GET['delete_car_id'])) {
    $car_id = $_GET['delete_car_id'];
    $deleteQuery = "DELETE FROM car WHERE car_id = $car_id";

    if ($mysqli->query($deleteQuery)) {
        header("Location: add_car.php"); // Redirect after successful deletion
        exit();
    } else {
        echo "Error deleting car: " . $mysqli->error;
    }
}

// Fetch cars added by the user
$cars = $mysqli->query("SELECT * FROM car WHERE user_id = " . $_SESSION['user_id']);
if (!$cars) {
    echo "Error fetching cars: " . $mysqli->error; // Handle fetch error
}

// Fetch brands and models for the dropdown
$brands = [];
$models = [];
$brandResult = $mysqli->query("SELECT DISTINCT brand FROM car");
$modelResult = $mysqli->query("SELECT DISTINCT model FROM car");

if ($brandResult) {
    while ($row = $brandResult->fetch_assoc()) {
        $brands[] = $row['brand'];
    }
}

if ($modelResult) {
    while ($row = $modelResult->fetch_assoc()) {
        $models[] = $row['model'];
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/css.css">
    <link href="../image/logo.png" rel="icon" />
    <title>Add Car</title>
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
<br>
<style>
    /* Button Styles */
.add {
    background-color: #28a745; /* Green background */
    color: white; /* White text */
    padding: 10px 20px; /* Padding for spacing */
    border: none; /* Remove border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Change cursor to pointer on hover */
    font-size: 16px; /* Font size */
    transition: background-color 0.3s, transform 0.2s; /* Smooth transition for hover effects */
    margin: 10px 0; /* Space above and below the button */
}

/* Button Hover Effect */
.add:hover {
    background-color: #218838; /* Darker green on hover */
    transform: scale(1.05); /* Slightly increase size on hover */
}

/* Button Focus Effect */
.add:focus {
    outline: none; /* Remove outline */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Add a blue shadow for focus */
}

</style>
    <!-- Button to add car -->
    &nbsp&nbsp <button class="add" onclick="document.getElementById('carForm').style.display='block'">Add Car</button>

    <!-- Form to add car -->
    <div id="carForm" style="display:none;">
        <form action="" method="post" enctype="multipart/form-data">
            <label for="name">Car Name:</label>
            <input type="text" name="name" required>

            <label for="brand">Brand:</label>
            <select name="brand" required>
                <option value="">Select Brand</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo $brand; ?>"><?php echo $brand; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="model">Model:</label>
            <select name="model" required>
                <option value="">Select Model</option>
                <?php foreach ($models as $model): ?>
                    <option value="<?php echo $model; ?>"><?php echo $model; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="price">Price:</label>
            <input type="number" name="price" required>

            <label for="description">Description:</label>
            <textarea name="description" required></textarea>

            <h4>Images/Videos (max 8, optional):</h4>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <input type="file" name="image<?php echo $i; ?>" accept="image/*,video/*">
            <?php endfor; ?>

            <button type="submit" name="add_car">Submit</button>
            <button type="button" onclick="document.getElementById('carForm').style.display='none'">Cancel</button>
        </form>
        <?php if (!empty($errorMsg)) echo "<p>$errorMsg</p>"; ?>
    </div>

  <!-- Display cars added by the user -->
<h2>&nbsp&nbspYour Cars</h2>
<div class="car-list">
    <?php while ($car = $cars->fetch_assoc()): ?>
        <div class="car-item">
            <img src="<?php echo $car['image1']; ?>" alt="<?php echo $car['name']; ?>" width="100">
            <h3><?php echo $car['name']; ?> (<?php echo $car['model']; ?>)</h3>
            <p>Brand: <?php echo $car['brand']; ?></p>
            <p>Price: $<?php echo $car['price']; ?></p>
            <p>Status: <?php echo ($car['is_sold'] === 'yes') ? "Sold" : "Not Sold"; ?></p> <!-- Display the "is_sold" status -->
            <button onclick="editCar(<?php echo $car['car_id']; ?>)">Edit</button>
            <button onclick="deleteCar(<?php echo $car['car_id']; ?>)">Delete</button>
        </div>
    <?php endwhile; ?>
</div>

<!-- Form to edit car -->
<div id="editCarForm" style="display:none;">
    <form id="carEditForm" action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="car_id" id="edit_car_id">
        <label for="edit_name">Car Name:</label>
        <input type="text" name="name" id="edit_name" required>

        <label for="edit_brand">Brand:</label>
        <select name="brand" id="edit_brand" required>
            <option value="">Select Brand</option>
            <?php foreach ($brands as $brand): ?>
                <option value="<?php echo $brand; ?>"><?php echo $brand; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="edit_model">Model:</label>
        <select name="model" id="edit_model" required>
            <option value="">Select Model</option>
            <?php foreach ($models as $model): ?>
                <option value="<?php echo $model; ?>"><?php echo $model; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="edit_price">Price:</label>
        <input type="number" name="price" id="edit_price" required>

        <label for="edit_description">Description:</label>
        <textarea name="description" id="edit_description" required></textarea>

        <label for="edit_is_sold">Sold Status:</label>
        <select name="is_sold" id="edit_is_sold" required>
            <option value="no">Not Sold</option>
            <option value="yes">Sold</option>
        </select>

        <h4>Images/Videos (max 8, optional):</h4>
        <?php for ($i = 1; $i <= 8; $i++): ?>
            <input type="file" name="image<?php echo $i; ?>" accept="image/*,video/*">
        <?php endfor; ?>

        <button type="submit" name="edit_car">Update</button>
        <button type="button" onclick="document.getElementById('editCarForm').style.display='none'">Cancel</button>
    </form>
</div>

<script>
    // Function to open edit car form
    function editCar(carId) {
        // Fetch car details using AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_car.php?car_id=" + carId, true);
        xhr.onload = function () {
            if (this.status === 200) {
                var car = JSON.parse(this.responseText);
                document.getElementById('edit_car_id').value = car.car_id;
                document.getElementById('edit_name').value = car.name;
                document.getElementById('edit_brand').value = car.brand;
                document.getElementById('edit_model').value = car.model;
                document.getElementById('edit_price').value = car.price;
                document.getElementById('edit_description').value = car.description;
                document.getElementById('edit_is_sold').value = car.is_sold; // Set the "is_sold" value
                document.getElementById('editCarForm').style.display = 'block';
            }
        };
        xhr.send();
    }

    // Function to confirm deletion
    function deleteCar(carId) {
        if (confirm("Are you sure you want to delete this car?")) {
            window.location.href = "?delete_car_id=" + carId;
        }
    }
</script>

</body>
</html>
