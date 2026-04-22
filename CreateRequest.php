<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $itemType = $_POST['itemType'] ?? '';
    $itemValue = $_POST['itemValue'] ?? '';
    $pickup = trim($_POST['pickupLocation'] ?? '');
    $dropoff = trim($_POST['dropoffLocation'] ?? '');
    $securityCode = $_POST['securityCode'] ?? '';

    $prices = [
        "jewelry" => 200,
        "cash" => 150,
        "electronics" => 120
    ];

    if (!isset($prices[$itemType])) {
        echo "<script>alert('❌ Invalid item type');</script>";
    } else {

        $price = $prices[$itemType];
        $insurance = 10000;
        $status = "Pending";
        $date = date("Y-m-d H:i:s");

        $requestID = rand(10000, 99999);

        // Linking add request to user ID
    if (isset($_SESSION['user_id'])) {
        $userID = $_SESSION['user_id'];
    } else {
            die("User not logged in");
    }   
            // البحث عن أول سائق متاح حسب الترتيب
    $driverID = null;

    $driverQuery = "SELECT DriverID FROM driver WHERE Status = 'Available' ORDER BY DriverID ASC LIMIT 1";
    $driverResult = $conn->query($driverQuery);

    if ($driverResult && $driverResult->num_rows > 0) {
        $driverRow = $driverResult->fetch_assoc();
        $driverID = $driverRow['DriverID'];

        // بما أنه تم ربط الطلب بسائق، تصير حالة الطلب In Transit
        $status = "In Transit";

        // تحديث حالة السائق إلى Busy
        $updateDriver = "UPDATE driver SET Status = 'Busy' WHERE DriverID = $driverID";
        $conn->query($updateDriver);
    } else {
        // إذا ما فيه أي سائق متاح
        $driverID = null;
        $status = "Pending";

        echo "<script>alert('Note: All drivers are currently busy. Your request will remain pending until a driver becomes available.');</script>";
    }       

        $stmt = $conn->prepare("
            INSERT INTO request
            (RequestID, ItemType, ItemValueRange, PickUpLocation, DropOffLocation, SecurityCode, ServicePrice, InsuranceCoverage, Status, CreationDate, UserID, DriverID)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "isssssdsssii",
            $requestID,
            $itemType,
            $itemValue,
            $pickup,
            $dropoff,
            $securityCode,
            $price,
            $insurance,
            $status,
            $date,
            $userID,
            $driverID
        );

        if ($stmt->execute()) {
            header("Location: MyRequests.php");
        } else {
            echo "<script>alert('❌ Database Error: " . addslashes($stmt->error) . "');</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - Create Request</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="create-request-container">
        <!--Log out -->
        <a href="login.html" class="logout-link">
            <img src="images/logout.png" alt="Logout" class="logout-icon">
        </a>

        <div class="container">
            <div class="logo-container">
                <img src="images/thamen.bmp" alt="Thamean Logo" class="logoSmall">
            </div>
            
            <h2 class="Home-title">Create Request Page</h2>
            <div class="divider"></div>
            
            <form action="CreateRequest.php" method="post" class="create-form" id="createRequestForm">
                <div class="input-group">
                    <select class="select-field" id="itemType" name="itemType" required>
                        <option value="" disabled selected>Select The Item Type</option>
                        <option value="jewelry">Jewelry</option>
                        <option value="cash">Cash</option>
                        <option value="electronics">High-value Electronics</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <select class="select-field" id="itemValue" name="itemValue" required>
                        <option value="" disabled selected>Select The Item Value Range</option>
                        <option value="less5000">Less than 5,000 SAR</option>
                        <option value="5000-10000">5,000 - 10,000 SAR</option>
                        <option value="more10000">More than 10,000 SAR</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <input type="text" id="pickupLocation" name="pickupLocation" placeholder="Pickup Location Ex: Riyadh, Al Olaya" class="input-field" required>
                </div>
                
                <div class="input-group">
                    <input type="text" id="dropoffLocation" name="dropoffLocation" placeholder="Dropoff Location Ex: Riyadh, Al Malaz" class="input-field" required>
                </div>
                
                <div class="input-group">
                    <input type="password" id="securityCode" name="securityCode" placeholder="Security Code (4 digits)" class="input-field" maxlength="4" required>
                    <div class="security-note">4-digit code (shared with receiver)</div>
                </div>
                
                <div class="price-box">
                    <div class="price-label">Service price:</div>
                    <div class="price-amount" id="priceAmount">-- SAR</div>
                </div>
                
                <div class="price-box" id="insuranceBox" style="background-color: rgba(255, 255, 255, 0.15);">
                    <div class="price-label">Insurance coverage:</div>
                    <div class="price-amount" id="insuranceAmount">Up to 10,000 SAR</div>
                </div>
                
                <button type="submit" class="login-btn">Create Request</button>
            </form>
        </div>
    </div>

    <script>
        // أسعار الخدمة حسب نوع المنتج
        const prices = {
            jewelry: 200,
            cash: 150,
            electronics: 120
        };
        
        // تحديث السعر
        function updatePrice() {
            let itemType = document.getElementById('itemType').value;
            let price = prices[itemType];
            let priceElement = document.getElementById('priceAmount');
            
            if (price) {
                priceElement.textContent = price + ' SAR';
            } else {
                priceElement.textContent = '-- SAR';
            }
        }
        
        // التحقق من وجود الموقع داخل الرياض
        function isInRiyadh(location) {
            let locationLower = location.toLowerCase();
            return locationLower.includes('riyadh');
        }
        
        // عرض رسالة التأمين إذا كانت القيمة أكثر من 10,000
        function checkInsuranceMessage() {
            let itemValue = document.getElementById('itemValue').value;
            let insuranceBox = document.getElementById('insuranceBox');
            
            if (itemValue === 'more10000') {
                alert("Note : Insurance coverage is limited to 10,000 SAR.");
            }
        }
        
        // تحديث السعر عند تغيير نوع المنتج
        document.getElementById('itemType').addEventListener('change', updatePrice);
        
        // التحقق من صحة البيانات عند الإرسال
        document.getElementById('createRequestForm').onsubmit = function(e) {
            e.preventDefault();
            
            // جلب القيم
            let itemType = document.getElementById('itemType').value;
            let itemValue = document.getElementById('itemValue').value;
            let pickup = document.getElementById('pickupLocation').value.trim();
            let dropoff = document.getElementById('dropoffLocation').value.trim();
            let securityCode = document.getElementById('securityCode').value;
            
            
            // التحقق من رمز الأمان (4 أرقام)
            if (securityCode.length !== 4 || isNaN(securityCode)) {
                alert("The security code must be 4 digits");
                return false;
            }
            
            // 3. التحقق من أن الموقع داخل الرياض
            if (!isInRiyadh(pickup)) {
                alert("The pickup location must be within Riyadh.");
                return false;
            }
            
            if (!isInRiyadh(dropoff)) {
                alert("The dropoff location must be within Riyadh.");
                return false;
            }
            
            // 4. عرض رسالة التأمين إذا كانت القيمة أكثر من 10,000
            if (itemValue === 'more10000') {
                alert("Note : Insurance coverage is limited to 10,000 SAR.");
            }
            
            // 5. حساب السعر
            let price = prices[itemType];
            
            // إنشاء رقم طلب عشوائي
            let orderNumber = Math.floor(Math.random() * 90000) + 10000;
            
            // عرض رسالة تأكيد مع رقم الطلب والسعر والتأمين
            let confirmMessage = "📦 Request Details:\n\n";
            confirmMessage += "Order Number: " + orderNumber + "\n";
            confirmMessage += "Service Price: " + price + " SAR\n";
            confirmMessage += "Insurance: Up to 10,000 SAR\n\n";
            confirmMessage += "Do you want to confirm this request?";
			
			let userConfirm = confirm(confirmMessage);
            
			if (userConfirm) {
                alert("✅ Request created successfully!");
                document.getElementById('createRequestForm').submit();
            } else {
                // User cancelled - do nothing
                return false;
            }
        };
        
        updatePrice();
    </script>
</body>
</html>