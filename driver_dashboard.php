<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['driver_id'])) {
    header("Location: login.html");
    exit();
}

$driverID = $_SESSION['driver_id'];

// جلب الطلب النشط للسائق (In Transit فقط)
$sql = "SELECT r.* FROM request r WHERE r.DriverID = ? AND r.Status = 'In Transit' LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driverID);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thameen - Driver Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">
    <div class="container">
        <div class="top-bar">
            <a href="login.html">
                <img src="images/logout.png" alt="Logout" style="width:40px; height:40px;">
            </a>
        </div>

        <div class="logo-container">
            <img src="images/thamen.bmp" alt="Thameen Logo" class="logo">
        </div>

        <h1 class="page-title">Driver Dashboard</h1>
        <div class="divider"></div>

        <div class="review-box" style="text-align: left;">
            <?php if ($request): ?>
                <div class="detail-item"><span class="detail-label">Request ID :</span><span class="detail-value"><?php echo $request['RequestID']; ?></span></div>
                <div class="detail-item"><span class="detail-label">Item Type :</span><span class="detail-value"><?php echo $request['ItemType']; ?></span></div>
                <div class="detail-item"><span class="detail-label">Pickup :</span><span class="detail-value"><?php echo $request['PickUpLocation']; ?></span></div>
                <div class="detail-item"><span class="detail-label">Dropoff :</span><span class="detail-value"><?php echo $request['DropOffLocation']; ?></span></div>
                <div class="detail-item"><span class="detail-label">Status :</span><span class="detail-value" style="color: #ffcc00;"><?php echo $request['Status']; ?></span></div>
                
                <form method="POST" action="complete_delivery.php" style="margin-top: 30px;">
                    <input type="hidden" name="request_id" value="<?php echo $request['RequestID']; ?>">
                    <button type="submit" class="btn" onclick="return confirm('Have you delivered the item and confirmed the security code?')">Mark as Delivered</button>
                </form>
            <?php else: ?>
                <p style="text-align: center; padding: 30px;">✅ No active deliveries. You will be assigned a new request when available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>