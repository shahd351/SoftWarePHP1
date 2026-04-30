<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'db_connection.php';

$userID = $_SESSION['user_id'] ?? null;
$requestID = $_GET['requestID'] ?? null;

if (!$userID) {
    die("User not logged in");
}

if (!$requestID) {
    die("Request ID not found");
}

$stmt = $conn->prepare("SELECT * FROM request WHERE RequestID = ? AND UserID = ?");
$stmt->bind_param("ii", $requestID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Request not found");
}

$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteRequest'])) {


    $deleteStmt = $conn->prepare("DELETE FROM request WHERE RequestID = ? AND UserID = ?");
    $deleteStmt->bind_param("ii", $requestID, $userID);
    $deleteStmt->execute();
    $driverID = $row['DriverID'];

if ($driverID !== null) {
    $updateDriver = $conn->prepare("UPDATE driver SET Status = 'Available' WHERE DriverID = ?");
    $updateDriver->bind_param("i", $driverID);
    $updateDriver->execute();
}

    echo "<script>alert('Request deleted successfully'); window.location.href='MyRequests.php';</script>";
    exit();
}


if ($row['ItemType'] === 'Jewelry' || $row['ItemType'] === 'jewelry') {
    $imagePath = 'images/jewelry.png';
} elseif ($row['ItemType'] === 'Cash' || $row['ItemType'] === 'cash') {
    $imagePath = 'images/money.png';
} else {
    $imagePath = 'images/electronics.png';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - Request Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="create-request-container">
        <a href="login.html" class="logout-link">
            <img src="images/logout.png" alt="Back" class="logout-icon" style="transform: rotate(180deg);">
        </a>

        <div class="container">
            <h2 class="Home-title">Request Details</h2>
            <div class="divider"></div>

            <div class="logo-container" style="margin-bottom: 20px;">
                <img src="<?php echo $imagePath; ?>" alt="Order Icon" style="width: 200px; height: 200px;">
            </div>

            <div class="details-content">
                <div class="detail-item">
                    <span class="detail-label">Request ID :</span>
                    <span class="detail-value"><?php echo $row['RequestID']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status :</span>
                    <span class="detail-value" id="statusText"><?php echo $row['Status']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Item Type :</span>
                    <span class="detail-value"><?php echo $row['ItemType']; ?></span>
                </div>
				<div class="detail-item">
                    <span class="detail-label">Item Value Range :</span>
                    <span class="detail-value"><?php echo $row['ItemValueRange']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Pickup Location :</span>
                    <span class="detail-value"><?php echo $row['PickUpLocation']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Drop-off Location :</span>
                    <span class="detail-value"><?php echo $row['DropOffLocation']; ?></span>
                </div>
				<div class="detail-item">
                    <span class="detail-label">Service Price :</span>
                    <span class="detail-value"><?php echo $row['ServicePrice']; ?> SAR</span>
                </div>
				<div class="detail-item">
                    <span class="detail-label">Insurance Coverage :</span>
                    <span class="detail-value"><?php echo $row['InsuranceCoverage']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Security Code :</span>
                    <span class="detail-value"><?php echo $row['SecurityCode']; ?></span>
                </div>
				<div class="detail-item">
                    <span class="detail-label">Created At :</span>
                    <span class="detail-value"><?php echo $row['CreationDate']; ?></span>
				</div>

<?php if ($row['Status'] !== 'Delivered') { ?>

<div class="edit-buttons-container" style="margin-top: 30px; display: flex; gap: 15px;">
    <a href="EditRequest.php?requestID=<?php echo $row['RequestID']; ?>"  class="role-btn" style="background-color: white; color: #5f1428; flex: 1;">
        Edit
    </a>

    <form method="post" style="flex: 1;">
        <button type="submit" name="deleteRequest" class="role-btn"
        onclick="return confirm('Are you sure you want to delete this request?')"
        style="background-color: #fefefe; color: #5f1428; flex: 1; width: 100%; border: none;">
            Delete
        </button>
   
    </form>
</div>

<?php } ?>

<?php if ($row['Status'] === 'Delivered') { ?>

<div class="edit-buttons-container" style="margin-top: 30px; display: flex; justify-content: center;">
    
    <a href="rate_review.php?requestID=<?php echo $row['RequestID']; ?>" 
       class="role-btn" 
       style="background-color: white; color: #5f1428; padding: 10px 20px; border-radius: 20px; text-decoration: none;">
       
       Rate & Review Page
       
    </a>

</div>

<?php } ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>