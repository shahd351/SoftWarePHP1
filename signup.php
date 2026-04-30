<?php
session_start();
include 'db_connection.php';

$error = "";
$success = "";
$fullName = $phone = $dob = $password = $confirmPassword = "";

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
    // التحقق من العمر
    else {
        $birthDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 18) {
            $error = "You must be at least 18 years old";
        } else {
            // التحقق من أن رقم الجوال غير مسجل
            $check = mysqli_query($conn, "SELECT * FROM User WHERE PhoneNumber='$phone'");
            if (mysqli_num_rows($check) > 0) {
                $error = "Phone number already registered";
            } else {
                // إضافة المستخدم
                $query = "INSERT INTO User (FullName, PhoneNumber, Password, DateOfBirth) 
                          VALUES ('$fullName', '$phone', '$password', '$dob')";
                
                if (mysqli_query($conn, $query)) {
                    $success = "Account created successfully! Please login.";
                    // تفريغ الحقول بعد النجاح
                    $fullName = $phone = $dob = $password = $confirmPassword = "";

                    echo "<script>setTimeout(function() { window.location.href = 'loginAsUser.php'; }, 2000);</script>";
                } else {
                    $error = "Database error: " . mysqli_error($conn);
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
        <p class="subtitle">Create your account to get started.</p>

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


        <form class="login-form" method="POST" id="signupForm">
            <div class="input-group">
                <input type="text" name="fullName" placeholder="FullName" id="fullName" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="text" name="phone" placeholder="PhoneNumber" id="phoneNumber" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="date" name="dob" placeholder="Date of Birth" id="dob" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" id="password" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="confirmPassword" placeholder="ConfirmPassword" id="confirmPassword" class="input-field" required>
            </div>
            
            <button type="submit" class="login-btn">SIGN UP</button>
        </form>
        
        <div class="signup-link">
            <a href="loginAsUser.php">Already Have a Account? Sign In</a>
        </div>
    </div>
	
	
	
</body>
</html>