
<?php
// edit_request.php - Allows editing only once, with Riyadh location validation
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must login first.'); window.location.href='login.html';</script>";
    exit;
}

$userID = $_SESSION['user_id'];
$requestID = isset($_GET['requestID']) ? (int)$_GET['requestID'] : 0;

if ($requestID == 0) {
    echo "<script>alert('Invalid request ID.'); window.location.href='MyRequests.php';</script>";
    exit;
}

require_once 'db_connection.php';

// Ensure is_edited column exists
$check = $conn->query("SHOW COLUMNS FROM request LIKE 'is_edited'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE request ADD is_edited TINYINT(1) NOT NULL DEFAULT '0'");
}

$stmt = $conn->prepare("SELECT *, IFNULL(is_edited,0) AS already_edited FROM request WHERE RequestID = ? AND UserID = ?");
$stmt->bind_param("ii", $requestID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Request not found or does not belong to you.'); window.location.href='MyRequests.php';</script>";
    exit;
}

$request = $result->fetch_assoc();

// Check restrictions
if ($request['Status'] == 'Delivered') {
    echo "<script>alert('Cannot edit a request that has already been delivered.'); window.location.href='RequestDetails.php?requestID=$requestID';</script>";
    exit;
}
if ($request['already_edited']) {
    echo "<script>alert('You have already edited this request. Only one edit is allowed.'); window.location.href='RequestDetails.php?requestID=$requestID';</script>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemType       = $_POST['itemType'];
    $itemValueRange = $_POST['itemValueRange'];
    $pickupLocation = trim($_POST['pickupLocation']);
    $dropoffLocation= trim($_POST['dropoffLocation']);
    $securityCode   = trim($_POST['securityCode']);
    
    // --- VALIDATION: Check that both locations contain "Riyadh" (case-insensitive) ---
    if (stripos($pickupLocation, 'Riyadh') === false) {
        echo "<script>alert('Error: Pickup location must be within Riyadh (include the word \"Riyadh\").'); window.history.back();</script>";
        exit;
    }
    if (stripos($dropoffLocation, 'Riyadh') === false) {
        echo "<script>alert('Error: Drop-off location must be within Riyadh (include the word \"Riyadh\").'); window.history.back();</script>";
        exit;
    }
    
    $prices = ['jewelry'=>200, 'cash'=>150, 'electronics'=>120];
    $servicePrice = $prices[$itemType] ?? 120;
    
    $update = $conn->prepare("UPDATE request 
                              SET ItemType=?, ItemValueRange=?, PickUpLocation=?, DropOffLocation=?, SecurityCode=?, ServicePrice=?, is_edited=1 
                              WHERE RequestID=? AND UserID=?");
    $update->bind_param("sssssiii", $itemType, $itemValueRange, $pickupLocation, $dropoffLocation, $securityCode, $servicePrice, $requestID, $userID);
    
    if ($update->execute()) {
        echo "<script>alert('✅ Changes saved successfully. You cannot edit this request again.'); window.location.href='RequestDetails.php?requestID=$requestID';</script>";
        exit;
    } else {
        echo "<script>alert('Error saving changes: " . addslashes($conn->error) . "'); window.location.href='RequestDetails.php?requestID=$requestID';</script>";
        exit;
    }
}

// If everything is fine, display the edit form
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Thamean - Edit Request</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Hide any arrows or extra icons */
        .request-arrow, .arrow { display: none !important; }
    </style>
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
        <h2 class="Home-title">Edit Request</h2>
        <div class="divider"></div>

        <form method="POST" class="create-form" id="editRequestForm">
            <div class="input-group">
                <label>Item Type :</label>
                <select name="itemType" id="itemType" class="input-field" onchange="updatePrice()" required>
                    <option value="jewelry" <?= $request['ItemType'] == 'Jewelry' ? 'selected' : '' ?>>Jewelry</option>
                    <option value="cash" <?= $request['ItemType'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="electronics" <?= $request['ItemType'] == 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                </select>
            </div>
            <div class="input-group">
                <label>Item Value Range :</label>
                <select name="itemValueRange" id="itemValue" class="input-field" onchange="checkValue()">
                    <option value="less5000" <?= $request['ItemValueRange'] == 'less5000' ? 'selected' : '' ?>>Less than 5,000 SAR</option>
                    <option value="5000-10000" <?= $request['ItemValueRange'] == '5000-10000' ? 'selected' : '' ?>>5,000 - 10,000 SAR</option>
                    <option value="more10000" <?= $request['ItemValueRange'] == 'more10000' ? 'selected' : '' ?>>More than 10,000 SAR</option>
                </select>
            </div>
            <div class="input-group">
                <label>Pickup Location :</label>
                <input type="text" name="pickupLocation" class="input-field" value="<?= htmlspecialchars($request['PickUpLocation']) ?>" required>
                <small style="color: gray;">Must be within Riyadh (include "Riyadh")</small>
            </div>
            <div class="input-group">
                <label>Dropoff Location :</label>
                <input type="text" name="dropoffLocation" class="input-field" value="<?= htmlspecialchars($request['DropOffLocation']) ?>" required>
                <small style="color: gray;">Must be within Riyadh (include "Riyadh")</small>
            </div>
            <div class="input-group">
                <label>Security Code :</label>
                <input type="password" name="securityCode" class="input-field" value="<?= htmlspecialchars($request['SecurityCode']) ?>" maxlength="4" required>
            </div>
            <div class="price-box">
                <span class="price-label">Service Price:</span>
                <span class="price-amount" id="priceDisplay"><?= $request['ServicePrice'] ?> SAR</span>
            </div>
            <div class="edit-buttons-container" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="role-btn" style="background-color: white; color: #5f1428; flex: 1;">Save Changes</button>
                <a href="RequestDetails.php?requestID=<?= $requestID ?>" class="role-btn" style="background-color: #fefefe; color: #5f1428; flex: 1; text-decoration: none; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function updatePrice() {
    const type = document.getElementById('itemType').value;
    const prices = { 'jewelry': '200 SAR', 'cash': '150 SAR', 'electronics': '120 SAR' };
    document.getElementById('priceDisplay').innerText = prices[type];
}
function checkValue() {
    if (document.getElementById('itemValue').value === 'more10000') {
        alert("Note: Insurance coverage is limited to 10,000 SAR.");
    }
}
window.onload = updatePrice;
</script>
</body>
</html>
