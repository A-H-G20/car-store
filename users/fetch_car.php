<?php
include('config.php'); // Include your database connection
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    $stmt = $mysqli->prepare("SELECT * FROM car WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
    echo json_encode($car);
}
?>
