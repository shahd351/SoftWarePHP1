<?php
session_start();
include 'db_connection.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM User WHERE PhoneNumber = '$phone' AND Password = '$password'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['user_name'] = $user['FullName'];
        header("Location: home.html");
        exit();
    } else {
        $error = "Invalid phone number or password";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thamean - User Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="images/thamen.bmp" alt="Thamean Logo" class="logoSmall">
        </div>
        
        <h2 class="login-title">Login</h2>
        <p class="subtitle">Sign in to continue.</p>
        
        <?php if (isset($error)) { ?>
                    <p style="color: #ffcccc; text-align: center;"><?php echo $error; ?></p>
                <?php } ?>

        <form class="login-form" method="post">
            <div class="input-group">
                <input type="text" placeholder="Phone Number" class="input-field" required>
            </div>
            
            <div class="input-group">
                <input type="password" placeholder="Password" class="input-field" required>
            </div>
            
            <button type="submit" class="login-btn">Log in</button>
        </form>
        
        <div class="signup-link">
            <a href="signup.html">Signup!</a>
        </div>
    </div>
</body>
</html>