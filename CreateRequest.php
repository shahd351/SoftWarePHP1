<?php
session_start();
include 'db_connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_SESSION['UserID'])) {
        echo "<script>alert('يرجى تسجيل الدخول أولاً'); window.location.href='login.html';</script>";
        exit();
    }

    $userID = $_SESSION['UserID'];
    $itemType = $_POST['itemType'];
    $itemValueRange = $_POST['itemValue'];
    $pickupLocation = $_POST['pickupLocation'];
    $dropoffLocation = $_POST['dropoffLocation'];
    $securityCode = $_POST['securityCode'];

    $prices = ['jewelry' => 200, 'cash' => 150, 'electronics' => 120];
    $servicePrice = isset($prices[$itemType]) ? $prices[$itemType] : 0;
    $insuranceCoverage = 10000;

    // فحص السائقين
    $sql_driver = "SELECT DriverID FROM driver WHERE Status = 'Available' LIMIT 1";
    $result_driver = mysqli_query($conn, $sql_driver);

    if (mysqli_num_rows($result_driver) > 0) {
        $driver_data = mysqli_fetch_assoc($result_driver);
        $assignedDriverID = $driver_data['DriverID'];
        mysqli_query($conn, "UPDATE driver SET Status = 'Busy' WHERE DriverID = $assignedDriverID");
        $requestStatus = "In Transit"; 
    } else {
        // حالة الفشل: يبقى في نفس الصفحة ويظهر التنبيه
        echo "<script>
                alert('نعتذر، جميع السائقين مشغولون الآن. يرجى المحاولة لاحقاً.');
                window.history.back(); 
              </script>";
        exit();
    }

    // إدخال الطلب
    $creationDate = date('Y-m-d H:i:s');
    $sql_insert = "INSERT INTO request (ItemType, ItemValueRange, PickUpLocation, DropOffLocation, SecurityCode, ServicePrice, InsuranceCoverage, Status, CreationDate, UserID, DriverID) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt, "sssssddssii", $itemType, $itemValueRange, $pickupLocation, $dropoffLocation, $securityCode, $servicePrice, $insuranceCoverage, $requestStatus, $creationDate, $userID, $assignedDriverID);

    if (mysqli_stmt_execute($stmt)) {
        // حالة النجاح: الانتقال لصفحة عرض الطلبات
        echo "<script>
                alert('تم إنشاء الطلب بنجاح!');
                window.location.href = 'MyRequests.php'; 
              </script>";
    } else {
        // في حال فشل الاستعلام: يبقى في نفس الصفحة
        echo "<script>
                alert('حدث خطأ أثناء حفظ الطلب، حاول مرة أخرى.');
                window.history.back();
              </script>";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
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
            
            <form action="" method="post" class="create-form" id="createRequestForm">
                <div class="input-group">
                    <select class="select-field" id="itemType" required>
                        <option value="" disabled selected>Select The Item Type</option>
                        <option value="jewelry">Jewelry</option>
                        <option value="cash">Cash</option>
                        <option value="electronics">High-value Electronics</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <select class="select-field" id="itemValue" required>
                        <option value="" disabled selected>Select The Item Value Range</option>
                        <option value="less5000">Less than 5,000 SAR</option>
                        <option value="5000-10000">5,000 - 10,000 SAR</option>
                        <option value="more10000">More than 10,000 SAR</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <input type="text" id="pickupLocation" placeholder="Pickup Location Ex: Riyadh, Al Olaya" class="input-field" required>
                </div>
                
                <div class="input-group">
                    <input type="text" id="dropoffLocation" placeholder="Dropoff Location Ex: Riyadh, Al Malaz" class="input-field" required>
                </div>
                
                <div class="input-group">
                    <input type="password" id="securityCode" placeholder="Security Code (4 digits)" class="input-field" maxlength="4" required>
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