<?php
    session_start();

    /*includes necessary functions*/
    include "secrets.php";
    include "php_functions/cart_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/user_functions.php";

    /*open a connection with both databases*/
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    /*if logged_in is not set, then its set to false and a guest userID is created*/
    if(!isset($_SESSION['logged_in'])) {
        $_SESSION['logged_in'] = false;
        $_SESSION['userID'] = assignUserID($database);
    }
?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/main_page.css">
        <title> Car Parts Catalog </title>
    </head>
    <body>
        <header>
            <h1>Car Parts Store</h1>
            <nav>
                <!--some links are only provided on the main page if the user is logged in, such
                  as the account details page (ru_page.php) and the sign out page (signout_page.php)-->
                <?php
                    if($_SESSION['logged_in'] == true) {
                ?>
                <a href="ru_page.php">Account</a>
                <a href="signout_page.php">Log out</a>
                <?php
                    } else {
                ?>
                <!--the log-in and sign-up page are only displayed if the user is not logged in-->
                <a href="signon_page.php">Login/Sign Up</a>
                <?php
                    }
                ?>
                <a href="esignon_page.php">Employee Login</a>
                <a href="cart.php">Cart</a>
            </nav>
        </header>
        <main>
            <div>
                <h2> Full Product List </h2>
                <div id="table-scroll">
                    <table>
                        <?php
                            /*access data from legacy database*/
                            $sql = $legacyDB->prepare("SELECT * FROM parts;");
                            if(!$sql) {
                                die("Error with Legacy DB SQL Query: " . $legacyDB->error);
                            }

                            $sql->execute();
                            /*legacyDB is looped through so that the table displays item data
                              which includes the picture, description, price, and weight.*/
                            while($row=$sql->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr>
                            <td> <img src='<?php echo $row['pictureURL'];?>'></td>
                            <td> <?php echo $row['description']; ?> <br>
                            $<?php echo $row['price']; ?> <br>
                            <?php echo $row['weight'];?> lbs <br>
                            <?php
                                /*Available quantity is found by searching the storedQuantity in the Products table
                                  and by comparing the legacy IDs to get the right item*/
                                $query = $database->prepare("SELECT * FROM Products WHERE legacyID = :legacyID");
                                if(!$query) {
                                    echo "Quantity not available right now!";
                                } else {
                                    $query->bindParam(':legacyID', $row['number']);
                                    $query->execute();
                                    $result = $query->fetch(PDO::FETCH_ASSOC);

                                    if($result && isset($result['storeQuantity'])) {
                                        echo "Available: " . $result['storeQuantity'];
                                    } else {
                                        echo "Available: N/A";
                                    }
                                }
                            ?> <br>
                            </td>
                            <td>
                                <!--each row has an 'Add to Cart' button for its corresponding item-->
                                <form action="main_page.php" method="POST">
                                    <input type="hidden" name="productID" value="<?php echo $result['productID'];?>">
                                    <label for="quantity">Quantity:</label>
                                    <input type="number" id="quantity" name="quantity" value="1" style="width:30px">
                                    <input type="submit" name="addToCart" value="Add to Cart">
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                            /*handles Add to Cart button*/
                            if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['addToCart'])) {

                                $productID = $_POST['productID'] ?? null;
                                $quantity = $_POST['quantity'] ?? 1;

                                /*checks that both needed variables are provided and valid*/
                                if($productID && $quantity > 0) {

                                  if(isValidQuantity($database, $productID, $quantity) == true) {

                                        /*checks if user is logged in so that we know whether to use userAccID or userID when adding and updating*/
                                        if($_SESSION['logged_in'] == true) {
                                            /*first check if the item being added into the cart is already in there, if it is we update quantity instead
                                              of adding multiples of an item in the cart*/
                                            $pQuery = $database->prepare("SELECT * FROM CustomerCart WHERE userAccID = :userAccID AND productID = :productID");
                                            $pQuery->bindParam(':userAccID', $_SESSION['userID'], PDO::PARAM_INT);
                                            $pQuery->bindParam(':productID', $productID, PDO::PARAM_INT);
                                            $pQuery->execute();
                                            $pResult = $pQuery->fetch(PDO::FETCH_ASSOC);

                                        /*if item is not already in the cart, then INSERT is used*/
                                        if(!$pResult) {
                                            $add = $database->prepare("INSERT INTO CustomerCart(productID, userAccID, quantity) VALUES (:productID, :userAccID, :quantity)");
                                            $add->bindParam(':productID', $productID, PDO::PARAM_INT);
                                            $add->bindParam(':userAccID', $_SESSION['userID'], PDO::PARAM_INT);
                                            $add->bindParam(':quantity', $quantity, PDO::PARAM_INT);

                                            if(!$add->execute()) {
                                                echo "Failed to add item to cart!";
                                            } 
                                        /*else UPDATE is used*/
                                        } else {
                                        $update = $database->prepare("UPDATE CustomerCart SET quantity = quantity + :quantity WHERE productID = :productID AND userAccID = :userAccID");
                                            $update->bindParam(':productID', $productID, PDO::PARAM_INT);
                                            $update->bindParam(':userAccID', $_SESSION['userID'], PDO::PARAM_INT);
                                            $update->bindParam(':quantity', $quantity, PDO::PARAM_INT);

                                            if(!$update->execute()) {
                                                echo "Failed to add item to cart!";
                                            } 
                                       }
                                    /*if the user is not logged in, then the above is repeated, except userID is used instead of userAccID*/
                                    } else {
                                        /*first see if item is already in the cart*/
                                        $pQuery = $database->prepare("SELECT * FROM CustomerCart WHERE userID = :userID AND productID = :productID");
                                        $pQuery->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
                                        $pQuery->bindParam(':productID', $productID, PDO::PARAM_INT);
                                        $pQuery->execute();
                                        $pResult = $pQuery->fetch(PDO::FETCH_ASSOC);

                                        /*if not in cart, INSERT into CustomerCart*/
                                        if(!$pResult) {
                                            $add = $database->prepare("INSERT INTO CustomerCart(productID, userID, quantity) VALUES (:productID, :userID, :quantity)");
                                            $add->bindParam(':productID', $productID, PDO::PARAM_INT);
                                            $add->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
                                            $add->bindParam(':quantity', $quantity, PDO::PARAM_INT);

                                            if(!$add->execute()) {
                                                echo "Failed to add item to cart!";
                                            }
                                        /*update cart if item is already in the cart*/
                                        } else {
                                            $update = $database->prepare("UPDATE CustomerCart SET quantity = quantity + :quantity WHERE productID = :productID AND userID = :userID");
                                            $update->bindParam(':productID', $productID, PDO::PARAM_INT);
                                            $update->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
                                            $update->bindParam(':quantity', $quantity, PDO::PARAM_INT);

                                            if(!$update->execute()) {
                                                echo "Failed to add item to cart!";
                                            }
                                       }
                                   }
                                  } else {
                                        echo "Item is unavailable";
                                  }
                                } else {
                                    echo "Something went wrong, please try again!";
                                }
                            }
                        ?>
                    </table>
                </div>
            </div>
        </main>
</body>
</html>


