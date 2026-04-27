> .:
<?php
/**
 * edit_request.php - Edit request only once, with inline error & alert popup.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

// Database connection
$host   = 'localhost';
$user   = 'root';
$pass   = 'root';
$db     = 'thamean';
$port   = 3306;
$conn   = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->set_charset("utf8");

// Authentication
if (!isset($_SESSION['UserID'])) {
    header("Location: login.html");
    exit;
}
$userID    = $_SESSION['UserID'];
$requestID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Auto-add is_edited column if missing
$check = $conn->query("SHOW COLUMNS FROM request LIKE 'is_edited'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE request ADD is_edited TINYINT(1) NOT NULL DEFAULT '0'");
}

// Fetch request details
$stmt = $conn->prepare("SELECT *, IFNULL(is_edited,0) AS already_edited FROM request WHERE RequestID=? AND UserID=?");
$stmt->bind_param("ii", $requestID, $userID);
$stmt->execute();
$result = $stmt->get_result();

$error   = '';
$message = '';
$request = null;
$showAlert = false; // for popup

if ($result->num_rows == 0) {
    $error = "Request not found or does not belong to you.";
    $showAlert = true;
} else {
    $request = $result->fetch_assoc();
    if ($request['Status'] == 'Delivered') {
        $error = "Cannot edit a request that has already been delivered.";
        $showAlert = true;
    } elseif ($request['already_edited']) {
        $error = "You have already edited this request. Only one edit is allowed.";
        $showAlert = true;
    }
}

// Handle form submission (only if editable)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && $request) {
    $itemType       = $_POST['itemType'];
    $itemValueRange = $_POST['itemValueRange'];
    $pickupLocation = trim($_POST['pickupLocation']);
    $dropoffLocation= trim($_POST['dropoffLocation']);
    $securityCode   = trim($_POST['securityCode']);
    $prices = ['jewelry'=>200, 'cash'=>150, 'electronics'=>120];
    $servicePrice = $prices[$itemType] ?? 120;

    $update = $conn->prepare("UPDATE request SET ItemType=?, ItemValueRange=?, PickUpLocation=?, DropOffLocation=?, SecurityCode=?, ServicePrice=?, is_edited=1 WHERE RequestID=? AND UserID=?");
    $update->bind_param("sssssiii", $itemType, $itemValueRange, $pickupLocation, $dropoffLocation, $securityCode, $servicePrice, $requestID, $userID);
    if ($update->execute()) {
        $message = "✅ Changes saved successfully. You cannot edit this request again.";
        // refresh local data
        $request['ItemType'] = $itemType;
        $request['ItemValueRange'] = $itemValueRange;
        $request['PickUpLocation'] = $pickupLocation;
        $request['DropOffLocation'] = $dropoffLocation;
        $request['SecurityCode'] = $securityCode;
        $request['ServicePrice'] = $servicePrice;
        $request['already_edited'] = 1;
    } else {
        $error = "Error saving changes: " . $conn->error;
        $showAlert = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <title>Thamean - Edit Request</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Hide any strange arrows that might appear */
        .request-arrow,
        .arrow,
        [class*="arrow"] {
            display: none !important;
        }
        /* Additional style for error/success messages */
        .error-msg, .success-msg {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .error-msg { background: #f8d7da; color: #721c24; }
        .success-msg { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
<div class="create-request-container">
    <a href="login.html" class="logout-link">
        <img src="images/logout.png" alt="Logout" class="logout-icon">
    </a>
    <div class="container">
        <div class="logo-container">
            <img src="images/thamen.

> .:
bmp" alt="Thamean Logo" class="logoSmall">
        </div>
        <h2 class="Home-title">Edit Request</h2>
        <div class="divider"></div>

        <?php if ($message): ?>
            <div class="success-msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($request && !$error): ?>
        <form method="POST" class="create-form" id="editRequestForm">
            <!-- Item Type -->
            <div class="input-group">
                <label>Item Type :</label>
                <select name="itemType" id="itemType" class="input-field" onchange="updatePrice()" required>
                    <option value="jewelry" <?= $request['ItemType'] == 'Jewelry' ? 'selected' : '' ?>>Jewelry</option>
                    <option value="cash" <?= $request['ItemType'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="electronics" <?= $request['ItemType'] == 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                </select>
            </div>
            <!-- Value Range -->
            <div class="input-group">
                <label>Item Value Range :</label>
                <select name="itemValueRange" id="itemValue" class="input-field" onchange="checkValue()">
                    <option value="less5000" <?= $request['ItemValueRange'] == 'less5000' ? 'selected' : '' ?>>Less than 5,000 SAR</option>
                    <option value="5000-10000" <?= $request['ItemValueRange'] == '5000-10000' ? 'selected' : '' ?>>5,000 - 10,000 SAR</option>
                    <option value="more10000" <?= $request['ItemValueRange'] == 'more10000' ? 'selected' : '' ?>>More than 10,000 SAR</option>
                </select>
            </div>
            <!-- Pickup -->
            <div class="input-group">
                <label>Pickup Location :</label>
                <input type="text" name="pickupLocation" class="input-field" value="<?= htmlspecialchars($request['PickUpLocation']) ?>" required>
            </div>
            <!-- Dropoff -->
            <div class="input-group">
                <label>Dropoff Location :</label>
                <input type="text" name="dropoffLocation" class="input-field" value="<?= htmlspecialchars($request['DropOffLocation']) ?>" required>
            </div>
            <!-- Security Code -->
            <div class="input-group">
                <label>Security Code :</label>
                <input type="password" name="securityCode" class="input-field" value="<?= htmlspecialchars($request['SecurityCode']) ?>" maxlength="4" required>
            </div>
            <!-- Price box -->
            <div class="price-box">
                <span class="price-label">Service Price:</span>
                <span class="price-amount" id="priceDisplay"><?= $request['ServicePrice'] ?> SAR</span>
            </div>
            <!-- Buttons -->
            <div class="edit-buttons-container" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="role-btn" style="background-color: white; color: #5f1428; flex: 1;">Save Changes</button>
                <a href="MyRequests.html" class="role-btn" style="background-color: #fefefe; color: #5f1428; flex: 1; text-decoration: none; text-align: center;">Cancel</a>
            </div>
        </form>
        <?php elseif (!$error && !$request): ?>
            <div class="error-msg">Request not available.</div>
        <?php endif; ?>
    </div>
</div>

<script>
// Show popup alert if there is a restriction error
<?php if ($showAlert && $error): ?>
    alert("<?= addslashes($error) ?>");
<?php endif; ?>

function updatePrice() {
    const type = document.getElementById('itemType').value;
    const prices = { 'jewelry': '200 SAR', 'cash': '150 SAR', 'electronics': '120 SAR' };
    document.getElementById('priceDisplay').innerText = prices[type];
}
function checkValue() {
    if (document.getElementById('itemValue').value === 'more10000') {

> .:
alert("Note: Insurance coverage is limited to 10,000 SAR.");
    }
}
window.onload = updatePrice;
</script>
</body>
</html>
