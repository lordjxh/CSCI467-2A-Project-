<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/cart_functions.php";

    session_start();
                
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
?>

<html>
    <head>
        <link rel="stylesheet" href="css/checkout_confirmation.css">
    </head>
    <body>
        <p class="user-message"><a href="ru_page.php" id="UserID" name="UserID" placeholder="Log In"><?php setLogOnAttributeValue($database) ?></a></p>
        <h1>2A-CORP</h1>
        <nav>
	        <a href="main_page.php">Home</a>
	        <a href="esignon_page.php">Staff</a>
	        <a href="cart.php">Cart</a>
        </nav>
        <div style="margin-top: 30px;"></div>
        <div class="left">
            <div class="section">
                <h2>Order Confirmed</h2>
                <p id="confirmation" name="confirmation"><?php echo "Your invoice ID is #" . $_SESSION['invoiceID'] . ". Please save for your records.";?></p>
                <p id="thanks-message" name="thanks-message">Thank you for shopping with us!</p>
            </div>
        </div>
    </body>
</html>
