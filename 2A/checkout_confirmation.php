<!-- 
    Group 2A - CSCI 467 Spring 2025
    checkout_confirmation.php - Not directly accessed by users, redirected after checkout is successful. Simple page that details
        the oder was successful and displays an invoice number for reference.

-->

<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/cart_functions.php";

    session_start();
            
    //establish connection(s) to database(s)
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
?>

<!-- Start of HTML Block -->

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/checkout_confirmation.css">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <title> Checkout </title>
    </head>
    <body>
        <?php setLogOnAttributeValue($database) ?>
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
