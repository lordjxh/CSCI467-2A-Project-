<!-- 
    Group 2A - CSCI 467 Spring 2025
    shipping_weights.php - This page is created for administrators to update shipping weights used during checkout. Weights have a minimum and maximum threshold that
        determines what percentage of the subtotal should be taken for shipping costs. This front-end includes safeguards to only allow administrators from accessing,
        and allows changing all aspects of the shipping weight values.

-->

<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/admin_functions.php";

    session_start();
    
    //establish connection(s) to database(s)
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    //if the user is not logged in as an admin, redirect to admin login
    if($_SESSION['isAdmin'] == false)
    {
        header("Location: esignon_page.php");
        exit();
    }

    //when form is submitted, update the shipping weights
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        setShippingWeights($database);
    }

    //call getShippingWeights() to create array of weights in ShippingWeights table
    $weights = getShippingWeights($database);
?>

<!-- Start of HTML Block -->

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <link rel="stylesheet" href="css/shipping_weights.css">
        <title>Shipping Weights</title>
    </head>
    <body>
        <header>
            <nav>
                <a href="admin_page.php" class="button"><- Go Back</a>
            </nav>
        </header>
        <div style="margin-top: 30px;"></div>
            <div class="left">
                <div class="section">
                    <h2>Modify Shipping Weights</h2><br/>
                    <?php printShippingWeights($weights); ?>
                </div>
            </div>
        </div>
    </body>
</html>