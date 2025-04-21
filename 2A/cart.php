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

    //assign the session ID to a current variable (will remove later)
    $userID = 10022;
    $_SESSION['userID'] = $userID;
    $_SESSION['logged_in'] = true;

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
    if($_SESSION['logged_in'] == true) //if the user is logged in, query by UserAccID
    {
        $cartQuery = "SELECT * FROM CustomerCart WHERE UserAccID = " . $userID . ";";
    }
    else //else query by UserID
    {
        $cartQuery = "SELECT * FROM CustomerCart WHERE UserID = " . $userID . ";";
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
        <p><a href="" id="UserID" name="UserID" placeholder="Log In"><?php setLogOnAttributeValue($database) ?></a></p>
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
                        if($cartItems != NULL)
                        {
                            printCart($cartItems, $database, true);
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
                <a href="checkout.php" class="button">Checkout</a>
            </div>
        </div>
        <script src="js/cart_script.js"></script>
    </body>
</html>