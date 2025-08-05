<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCash System Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="loading-screen" id="loadingScreen">
        <div class="spinner"></div>
        <div class="loading-title">SmartCash System Portal</div>
        <div class="loading-footer" style="font-size:0.85em;color:#555;margin-top:10px;text-align:center;">
            Created by Thanuja Dilshan - HNDIT 2022. All rights reserved.
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <header class="app-header">
            <img src="images/header.png" alt="SmartCash System Header" class="app-header-image">
            
        </header>

        <h1>Welcome to SmartCash!</h1>
        <p>Please select your login type:</p>
        <div>
            <button class="login-button" onclick="location.href='admin_login.php'">Main Admin Login</button>
            <button class="login-button" onclick="location.href='common_user_login.php'">Other User Login</button>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date("Y"); ?> SmartCash
    </div>

    <script src="js/scripts.js"></script>

</body>
</html>