<html>
    <head>
        <?php
            include "secrets.php";
            include "php_functions/database_functions.php";
            include "php_functions/cart_functions.php";

            session_start();
                
            $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
            $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
            $userID = 10022;

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
                printCart($output, true);
            }
            else //otherwise the cart is empty
            {
                echo "<p>Cart Empty</p>";
            }

            //Website's footers

        ?>

        <a href="checkout.php">
            <button>Checkout</button>
        </a>
    </body>
</html>