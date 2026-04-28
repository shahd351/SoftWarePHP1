<?php
session_start();
include 'db_connection.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM Admin WHERE UserName = '$username' AND Password = '$password'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_id'] = $admin['AdminID'];
        $_SESSION['admin_name'] = $admin['UserName'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="images/thamen.bmp" alt="Thamean Logo" class="logoSmall">
        </div>
        
        <h2 class="login-title">Login</h2>
        <p class="subtitle">Sign in to continue.</p>
        
        <?php if ($error != "") { ?>
            <div style="background-color: #ffebee; border: 1px solid #ff1744; border-radius: 25px; padding: 12px; margin-bottom: 20px; text-align: center;">
                <span style="color: #ff1744; font-weight: bold;">⚠️ <?php echo $error; ?></span>
            </div>
        <?php } ?>

        <form class="login-form" method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="username" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" class="input-field" required>
            </div>
            
            <button type="submit" class="login-btn">Log in</button>
        </form>
    </div>
</body>
</html>