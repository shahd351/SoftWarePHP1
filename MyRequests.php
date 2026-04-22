<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$userID = $_SESSION['user_id'];

$sql = "SELECT * FROM request WHERE UserID = ? ORDER BY CreationDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - My Requests</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="create-request-container">
        <a href="login.html" class="logout-link">
            <img src="images/logout.png" alt="Logout" class="logout-icon">
        </a>

        <div class="container">
            <div class="logo-container">
                <img src="images/thamen.bmp" alt="Thamean Logo" class="logoSmall">
            </div>
            
            <h2 class="Home-title">My Requests Page</h2>
            <div class="divider"></div>

            <div class="requests-list">

<?php if ($result->num_rows > 0) { ?>
    
    <?php while ($row = $result->fetch_assoc()) { ?>

        <?php
        if ($row['Status'] === 'Delivered') {
            $displayStatus = 'Delivered';
            $statusClass = 'delivered';
            $detailsPage = 'RequestDetailsAfter.html';
        } elseif (is_null($row['DriverID'])) {
            $displayStatus = 'Pending';
            $statusClass = 'pending';
            $detailsPage = 'RequestDetails.html';
        } else {
            $displayStatus = 'In Transit';
            $statusClass = 'transit';
            $detailsPage = 'RequestDetails.html';
        }

        if ($row['ItemType'] === 'Jewelry' || $row['ItemType'] === 'jewelry') {
            $imagePath = 'images/jewelry.png';
            $imageAlt = 'Jewelry';
        } elseif ($row['ItemType'] === 'Cash' || $row['ItemType'] === 'cash') {
            $imagePath = 'images/money.png';
            $imageAlt = 'Money';
        } else {
            $imagePath = 'images/electronics.png';
            $imageAlt = 'Electronics';
        }
                $detailsPage = 'RequestDetails.php?requestID=' . $row['RequestID'];

        ?>

        <a href="<?php echo $detailsPage; ?>" class="request-link-wrapper">
            <div class="price-box request-card">
                <div class="request-card-content">
                    <div class="request-details-text">
                        <span class="request-id">Request ID: <?php echo $row['RequestID']; ?></span>
                        <span class="request-status <?php echo $statusClass; ?>">
                            <?php echo $displayStatus; ?>
                        </span>
                    </div>
                    <div class="request-details-image">
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo $imageAlt; ?>">
                    </div>
                </div>
                <div class="request-arrow"></div>
            </div>
        </a>

    <?php } ?>

<?php } else { ?>

    <div class="price-box request-card">
        <div class="request-card-content">
            <div class="request-details-text">
                <span style="font-size:18px; font-weight:bold; color:white;">You don’t have any requests yet</span>
                <a href="CreateRequest.php" style="font-size:18px; font-weight:bold; color:white;">
                 Create your first request
                </a>        
            </div>
        </div>
    </div>

<?php } ?>

</div>
        </div>
    </div>

    <?php
$stmt->close();
$conn->close();
?>
</body>
</html>