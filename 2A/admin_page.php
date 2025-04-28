<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/admin_functions.php";

    session_start();
    
    //establish connection(s) to database(s)
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    //for debug purposes, remove when finalizing
    $_SESSION['isAdmin'] = true;

    //if the user is not logged in as an admin, redirect to admin login
    if($_SESSION['isAdmin'] == false)
    {
        header("Location: esignon_page.php");
        exit();
    }
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/admin_page.css">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <title> Admin </title>
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
                    <h2>Administrator Page</h2><br/>
                    <a class="button" href="">Invoice Lookup</a>
                    <a class="button" href="shipping_weights.php">Modify Shipping Weights</a>
                </div>
            </div>
        </div>
    </body>
</html>