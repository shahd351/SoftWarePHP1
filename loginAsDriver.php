<?php
session_start();
include 'db_connection.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = $_POST['fullName'];
    $nationalId = $_POST['nationalId'];
    
    $sql = "SELECT * FROM Driver WHERE FullName = '$fullName' AND NationalID = '$nationalId'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $driver = $result->fetch_assoc();
        $_SESSION['driver_id'] = $driver['DriverID'];
        $_SESSION['driver_name'] = $driver['FullName'];
        $_SESSION['driver_national_id'] = $driver['NationalID'];
        header("Location: driver_dashboard.php");
        exit();
    } else {
        $error = "Invalid Name or National ID";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thameen - Driver Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="images/thamen.bmp" alt="Thameen Logo" class="logoSmall">
        </div>
        
        <h2 class="login-title">Driver Login</h2>
        <p class="subtitle">Sign in to view your assigned deliveries.</p>
        
        <?php if ($error != "") { ?>
            <div style="background-color: #ffebee; border: 1px solid #ff1744; border-radius: 25px; padding: 12px; margin-bottom: 20px; text-align: center;">
                <span style="color: #ff1744; font-weight: bold;">⚠️ <?php echo $error; ?></span>
            </div>
        <?php } ?>

        <form class="login-form" id="driverLoginForm" method="POST">
            <div class="input-group">
                <input type="text" id="driverFullName" name="fullName" placeholder="Full Name" class="input-field" required>
            </div>

            <div class="input-group">
                <input type="text" id="driverNationalId" name="nationalId"placeholder="National ID" class="input-field" required>
            </div>
            
            <button type="submit" class="login-btn">Log in</button>
        </form>

    </div>

</body>
</html>