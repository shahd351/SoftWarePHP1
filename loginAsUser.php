<?php
session_start();
include 'db_connection.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['phone']) && isset($_POST['password'])) {
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        
        $sql = "SELECT * FROM User WHERE PhoneNumber = '$phone' AND Password = '$password'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['user_name'] = $user['FullName'];
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid entries. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thameen - User Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="images/thamen.bmp" alt="Thameen Logo" class="logoSmall">
        </div>
        
        <h2 class="login-title">Login</h2>
        <p class="subtitle">Sign in to continue.</p>
        
        <?php if (isset($error) && $error != "") { ?>
            <div style="background-color: #ffebee; border: 1px solid #ff1744; border-radius: 25px; padding: 12px; margin-bottom: 20px; text-align: center;">
                <span style="color: #ff1744; font-weight: bold;">⚠️ <?php echo $error; ?></span>
            </div>
        <?php } ?>

        <form class="login-form" method="post">
            <div class="input-group">
                <input type="text" name="phone" placeholder="Phone Number" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" class="input-field" required>
            </div>
            
            <button type="submit" class="login-btn">Log in</button>
        </form>
        
        <div class="signup-link">
            <a href="signup.php">Signup!</a>
        </div>
    </div>
</body>
</html>