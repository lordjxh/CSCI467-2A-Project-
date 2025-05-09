<!-- 
    Group 2A - CSCI 467 Spring 2025
    cart.php - The front-end for a user's shopping cart. Shows items that are added from the main page. Also allows user to modify their
        quantity amounts, and remove items. Checks if quantities are valid, and flags invalid items and prevents proceeding with
        checkout until amounts are fixed.

-->

<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/cart_functions.php";

    session_start();
          
    //establish connection(s) to database(s)
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
            
    //if a userID has not been assigned, call assignUserID() to create an ID
    if($_SESSION['userID'] == NULL)
    {
        $_SESSION['userID'] = assignUserID($database);
        $_SESSION['logged_in'] = false;
    }

    //handles changes to a cart item upon form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $originalQuantity = $_POST['quantity'];
        $productID = $_POST['productID'];

        if (isset($_POST['increase'])) //handles increasing the quantity of an item
        {
            if($_SESSION['logged_in'] == true)
            {
                $changeStatement = "UPDATE CustomerCart SET quantity = " . ($originalQuantity + 1) . " WHERE userAccID = " . $_SESSION['userID'] . " AND ProductID = " . $productID . ";";
            }
            else
            {
                $changeStatement = "UPDATE CustomerCart SET quantity = " . ($originalQuantity + 1) . " WHERE userID = " . $_SESSION['userID'] . " AND ProductID = " . $productID . ";";
            }

            updateDatabaseValue($database, $changeStatement);
        }
        else if($originalQuantity - 1 <= 0 || isset($_POST['remove'])) //handles removing an item from the cart
        {
            if($_SESSION['logged_in'] == true)
            {
                $removeStatement = "DELETE FROM CustomerCart WHERE ProductID = " . $productID . " AND userAccID = " . $_SESSION['userID'] . ";";
            }
            else
            {
                $removeStatement = "DELETE FROM CustomerCart WHERE ProductID = " . $productID . " AND userID = " . $_SESSION['userID'] . ";";
            }

            updateDatabaseValue($database, $removeStatement);
        }
        else if (isset($_POST['decrease'])) //handles decreasing the quantity of an item
        {
            if($_SESSION['logged_in'] == true)
            {
                $changeStatement = "UPDATE CustomerCart SET quantity = " . ($originalQuantity - 1) . " WHERE userAccID = " . $_SESSION['userID'] . " AND ProductID = " . $productID . ";";
            }
            else
            {
                $changeStatement = "UPDATE CustomerCart SET quantity = " . ($originalQuantity - 1) . " WHERE userID = " . $_SESSION['userID'] . " AND ProductID = " . $productID . ";";
            }

            updateDatabaseValue($database, $changeStatement);
        }
    }

    //fetches cart contents and prints to front-end
    if($_SESSION['logged_in'] == true) //if the user is logged in, query by UserAccID
    {
        $cartQuery = "SELECT * FROM CustomerCart WHERE UserAccID = " . $_SESSION['userID'] . ";";
    }
    else //else query by UserID
    {
        $cartQuery = "SELECT * FROM CustomerCart WHERE UserID = " . $_SESSION['userID'] . ";";
    }

    $rs = getSQL($database, $cartQuery);
    $cartItems = getCartContents($rs, $database, $legacyDB);

    $_SESSION['cart'] = $cartItems; //force cart contents to session variable
?>

<!-- Start of HTML Block -->

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/cart.css">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <title> Cart </title>
    </head>
    <body>
        <h1>Car Parts Store</h1>
        <nav>
	        <a href="main_page.php"><- Go Back</a>
	        <?php setLogOnAttributeValue($database); ?>
        </nav>
        <div style="margin-top: 30px;"></div>
        <div class="left">
            <div class="section">
                <h2>Cart</h2>
                <?php
                    //If a user is established, print the cart contents (if any)
                    if($_SESSION['userID'] != NULL)
                    {
                        if($cartItems != NULL)
                        {
                            printCart($cartItems, true);
                            printTotals($cartItems, $database);
                        }
                        else
                        {
                            echo "<p>The cart is empty.</p>";
                        }
                    }
                    else //otherwise print error, as userID failed to populate
                    {
                        echo "<p>ERROR: failed to assign a User ID. Please reload from the home page.</p>";
                    }
                ?>
                <button id="checkout" name="checkout" href="checkout.php" class="button">Checkout</button>
                </br>
                <div id="checkoutMessage" name="checkoutMessage" style="color: red; font-size: 0.9em; margin-top: 4px;"></div>
            </div>
        </div>
        <script src="js/cart_script.js"></script>
    </body>
</html>