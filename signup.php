<?php
session_start();
include 'db_connection.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = $_POST['fullName'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // التحقق من تطابق كلمة المرور
    if ($password !== $confirmPassword) {
        $error = "Password does not match";
    }
    // التحقق من طول كلمة المرور
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters";
    }
    // التحقق من العمر (18+)
    else {
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        echo "Age: " . $age; 

        if ($age < 18) {
            $error = "You must be at least 18 years old to register";
        } else {
            // التحقق من أن رقم الجوال غير مسجل مسبقاً
            $checkSql = "SELECT * FROM User WHERE PhoneNumber = '$phone'";
            $checkResult = $conn->query($checkSql);
            
            if ($checkResult->num_rows > 0) {
                $error = "Phone number already registered";
            } else {
                // إضافة المستخدم الجديد
                $sql = "INSERT INTO User (FullName, PhoneNumber, Password, DateOfBirth) 
                        VALUES ('$fullName', '$phone', '$password', '$dob')";
                
                if ($conn->query($sql) === TRUE) {
                    $success = "Account created successfully! Please login.";
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="images/thamen.bmp" alt="Thamean Logo" class="logoSmall">
        </div>
        
        <h2 class="login-title">Sign Up!</h2>
        
        <?php if ($error != "") { ?>
            <div style="background-color: #ffebee; border: 1px solid #ff1744; border-radius: 25px; padding: 12px; margin-bottom: 20px; text-align: center;">
                <span style="color: #ff1744; font-weight: bold;">⚠️ <?php echo $error; ?></span>
            </div>
        <?php } ?>
        
        <?php if ($success != "") { ?>
            <div style="background-color: #d4edda; border: 1px solid #28a745; border-radius: 25px; padding: 12px; margin-bottom: 20px; text-align: center;">
                <span style="color: #28a745; font-weight: bold;">✅ <?php echo $success; ?></span>
            </div>
        <?php } ?>


        <form class="login-form" method="POST">
            <div class="input-group">
                <input type="text" name="fullName" placeholder="FullName" id="fullName" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="text" placeholder="PhoneNumber" id="phoneNumber" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="date" placeholder="Date of Birth" id="dob" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="password" placeholder="Password" id="password" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="password" placeholder="ConfirmPassword" id="confirmPassword" class="input-field" required>
            </div>
            
            <button type="submit" class="login-btn">SIGN UP</button>
        </form>
        
        <div class="signup-link">
            <a href="loginAsUser.html">Already Have a Account? Sign In</a>
        </div>
    </div>
	
	
	 <script>
        document.getElementById('signupForm').onsubmit = function(e) {
            e.preventDefault();
            
            // حساب العمر
            let dob = document.getElementById('dob').value;
            let birthDate = new Date(dob);
            let today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            let monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            // التحقق من العمر
            if (age < 18) {
                alert("You are under 18 years old, you cannot register.");
                return false;
            }
            
            // التحقق من تطابق كلمة المرور
            let password = document.getElementById('password').value;
            let confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                alert("The password does not match.");
                return false;
            }
            
            // التحقق من طول كلمة المرور
            if (password.length < 8) {
                alert("The password must be at least 8 characters long.");
                return false;
            }
            
            // اذا كل شي صح انتقل لصفحه الدخول
            window.location.href = "loginAsUser.html";
        };
    </script>
	
	
</body>
</html>