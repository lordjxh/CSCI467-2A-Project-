<html>
    <head>
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
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        ?>
    </head>
    <body>
        <?php
            //Website's default headers/navigation


            //If a user is established, retrive cart's contents
            if($userID != NULL)
            {
                $cartQuery = "SELECT * FROM CustomerCart WHERE UserAccID = " . $userID . ";";
                $rs = getSQL($database, $cartQuery);
                $output = getCartContents($rs, $database, $legacyDB);

                $_SESSION['cart'] = $output; //force cart contents to session variable
                
                if($output != NULL)
                {
                    printCart($output, true);
                }
                else
                {
                    echo "<p>Cart Empty</p>";
                }
            }
            else //otherwise print error, as userID failed to populate
            {
                echo "<p>ERROR: failed to assign a User ID. Please reload from the home page.</p>";
            }

            //Website's footers

        ?>

        <a href="checkout.php">
            <button>Checkout</button>
        </a>
    </body>
</html>