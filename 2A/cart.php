<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/cart_functions.php";

    session_start();
                
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
            
    //if a userID has not been assigned, call assignUserID() to create an ID
    if($_SESSION['userID'] == NULL)
    {
        $_SESSION['userID'] = assignUserID($database);
        $_SESSION['logged_in'] = false;
    }

    //forces usage of ID 10022 for testing purposes (remove when finalizing project)
    //$_SESSION['userID'] = 10022;
    //$_SESSION['logged_in'] = true;

    //handles changes to a cart item upon form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $originalQuantity = $_POST['quantity'];
        $productID = $_POST['productID'];

        if (isset($_POST['increase']))
        {
            $changeStatement = "UPDATE CustomerCart SET quantity = " . ($originalQuantity + 1) . " WHERE UserAccID = " . $_SESSION['userID'] . " AND ProductID = " . $productID . ";";
            updateDatabaseValue($database, $changeStatement);
        }
        else if($originalQuantity - 1 <= 0 || isset($_POST['remove']))
        {
            $removeStatement = "DELETE FROM CustomerCart WHERE ProductID = " . $productID . " AND UserAccID = " . $_SESSION['userID'] . ";";
            updateDatabaseValue($database, $removeStatement);
        }
        else if (isset($_POST['decrease']))
        {
            $changeStatement = "UPDATE CustomerCart SET quantity = " . ($originalQuantity - 1) . " WHERE UserAccID = " . $_SESSION['userID'] . " AND ProductID = " . $productID . ";";
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

<html>
    <head>
        <link rel="stylesheet" href="css/cart.css">
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
                <h2>Cart</h2>
                <?php
                    //If a user is established, print the cart contents (if any)
                    if($_SESSION['userID'] != NULL)
                    {
                        if($cartItems != NULL)
                        {
                            printCart($cartItems, $database, true);
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