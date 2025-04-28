<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/admin_functions.php";

    session_start();
                
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        setShippingWeights($database);
    }

    $weights = getShippingWeights($database);
?>

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
                    <?php printShippingWeights($weights); ?>
                </div>
            </div>
        </div>
    </body>
</html>