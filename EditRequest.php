<?php
// edit_request.php - Allows user to edit their request only once
require_once 'config.php';
checkLogin();

$userID = $_SESSION['UserID'];
$requestID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// 1. Automatically add is_edited column if it doesn't exist
$check_col = "SHOW COLUMNS FROM request LIKE 'is_edited'";
$col_res = mysqli_query($conn, $check_col);
if (mysqli_num_rows($col_res) == 0) {
    $alter = "ALTER TABLE request ADD is_edited TINYINT(1) NOT NULL DEFAULT '0'";
    mysqli_query($conn, $alter);
}

// 2. Fetch request data and check ownership
$query = "SELECT r.*, 
          IFNULL(r.is_edited, 0) AS already_edited
          FROM request r
          WHERE r.RequestID = $requestID AND r.UserID = $userID";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die("Request not found or does not belong to you.");
}
$request = mysqli_fetch_assoc($result);

// 3. Prevent editing if already delivered or already edited once
if ($request['Status'] == 'Delivered') {
    die("Cannot edit a request that has already been delivered.");
}
if ($request['already_edited']) {
    die("You have already edited this request. Only one edit is allowed.");
}

// 4. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemType       = mysqli_real_escape_string($conn, $_POST['itemType']);
    $itemValueRange = mysqli_real_escape_string($conn, $_POST['itemValueRange']);
    $pickupLocation = mysqli_real_escape_string($conn, trim($_POST['pickupLocation']));
    $dropoffLocation= mysqli_real_escape_string($conn, trim($_POST['dropoffLocation']));
    $securityCode   = mysqli_real_escape_string($conn, trim($_POST['securityCode']));
    
    // Calculate service price based on item type
    $prices = ['jewelry'=>200, 'cash'=>150, 'electronics'=>120];
    $servicePrice = isset($prices[$itemType]) ? $prices[$itemType] : 120;
    
    $update = "UPDATE request 
               SET ItemType = '$itemType',
                   ItemValueRange = '$itemValueRange',
                   PickUpLocation = '$pickupLocation',
                   DropOffLocation = '$dropoffLocation',
                   SecurityCode = '$securityCode',
                   ServicePrice = $servicePrice,
                   is_edited = 1
               WHERE RequestID = $requestID AND UserID = $userID";
    
    if (mysqli_query($conn, $update)) {
        $message = "✅ Changes saved successfully. You cannot edit this request again.";
        // Update displayed data
        $request['ItemType'] = $itemType;
        $request['ItemValueRange'] = $itemValueRange;
        $request['PickUpLocation'] = $pickupLocation;
        $request['DropOffLocation'] = $dropoffLocation;
        $request['SecurityCode'] = $securityCode;
        $request['ServicePrice'] = $servicePrice;
        $request['already_edited'] = 1;
    } else {
        $error = "Error saving changes: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - Edit Request</title>
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
        <h2 class="Home-title">Edit Request</h2>
        <div class="divider"></div>

        <?php if($message): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; border-radius:8px; margin-bottom:15px;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:15px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
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
            </div>

            <div class="input-group">
                <label>Dropoff Location :</label>
                <input type="text" name="dropoffLocation" class="input-field" value="<?= htmlspecialchars($request['DropOffLocation']) ?>" required>
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
                <a href="MyRequests.html" class="role-btn" style="background-color: #fefefe; color: #5f1428; flex: 1; text-decoration: none; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Update displayed price when item type changes
function updatePrice() {
    const type = document.getElementById('itemType').value;
    const prices = { 'jewelry': '200 SAR', 'cash': '150 SAR', 'electronics': '120 SAR' };
    document.getElementById('priceDisplay').innerText = prices[type];
}
// Warn user if item value exceeds insurance coverage
function checkValue() {
    if (document.getElementById('itemValue').value === 'more10000') {
        alert("Note: Insurance coverage is limited to 10,000 SAR.");
    }
}
window.onload = updatePrice;
</script>
</body>
</html>
