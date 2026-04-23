<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['driver_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $requestID = $_POST['request_id'];
    $driverID = $_SESSION['driver_id'];
    
    // تحديث حالة الطلب إلى Delivered
    $updateRequest = $conn->prepare("UPDATE request SET Status = 'Delivered' WHERE RequestID = ? AND DriverID = ?");
    $updateRequest->bind_param("ii", $requestID, $driverID);
    $updateRequest->execute();
    
    // تحديث حالة السائق إلى Available
    $updateDriver = $conn->prepare("UPDATE driver SET Status = 'Available' WHERE DriverID = ?");
    $updateDriver->bind_param("i", $driverID);
    $updateDriver->execute();
    
    header("Location: driver_dashboard.php");
    exit();
} else {
    header("Location: driver_dashboard.php");
    exit();
}
?>