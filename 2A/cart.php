<?php
    include "secrets.php";
    include "php_functions/database_functions.php";
    include "php_functions/cart_functions.php";

    session_start();
                
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
            
    //need a way to determine if a user has an account or is a guest user
    $_SESSION['userID'] = 10022;
    $userID = $_SESSION['userID'];

    //handles changes to a cart item upon form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $newQuantity = $_POST['quantity'];
        $productID = $_POST['productID'];

        if (isset($_POST['increase']))
        {
            $changeStatement = "UPDATE CustomerCart SET quantity = " . ($newQuantity + 1) . " WHERE UserAccID = " . $userID . " AND ProductID = " . $productID . ";";
            updateDatabaseValue($database, $changeStatement);
        }
        else if (isset($_POST['decrease']))
        {
            $changeStatement = "UPDATE CustomerCart SET quantity = " . ($newQuantity - 1) . " WHERE UserAccID = " . $userID . " AND ProductID = " . $productID . ";";
            updateDatabaseValue($database, $changeStatement);
        }
        else if (isset($_POST['remove']))
        {
            $removeStatement = "DELETE FROM CustomerCart WHERE ProductID = " . $productID . " AND UserAccID = " . $userID . ";";
            updateDatabaseValue($database, $removeStatement);
        }
    }

    //fetches cart contents and prints to front-end
    $cartQuery = "SELECT * FROM CustomerCart WHERE UserAccID = " . $userID . ";";
    $rs = getSQL($database, $cartQuery);
    $output = getCartContents($rs, $database, $legacyDB);

    $_SESSION['cart'] = $output; //force cart contents to session variable
?>

<html>
    <head>
        <link rel="stylesheet" href="css/cart.css">
    </head>
    <body>
        <h1>2A-CORP</h1>
        <nav>
	        <a href=>Home</a>
	        <a href=>Staff</a>
	        <a href=>Cart</a>
        </nav>
        <div style="margin-top: 30px;"></div>
        <div class="left">
            <div class="section">
                <h2>Cart</h2>
                <?php
                    //If a user is established, print the cart contents (if any)
                    if($userID != NULL)
                    {
                        printCart($output, $database, true);
                    }
                    else //otherwise print error, as userID failed to populate
                    {
                        echo "<p>ERROR: failed to assign a User ID. Please reload from the home page.</p>";
                    }
                ?>
                <a href="checkout.php" class="button">Checkout</a>
            </div>
        </div>
        <script src="js/cart_script.js"></script>
    </body>
</html>