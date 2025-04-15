<?php
    session_start();

    /*if a user hasn't signed in yet, 'logged_in' should be set to false*/
    if(!isset($_SESSION['logged_in'])) {
        $_SESSION['logged_in'] = false;
    }

    /*open a connection with our database and legacy database*/
    include "secrets.php";
    include "database_functions.php";

    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/main_page.css">
        <title> Catalog </title>
    </head>
    <body>
        <header>
            <h1>Car Parts Store</h1>
            <div class="navbar">
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
            </div>
        </header>
        <main>
            <div>
                <h2> Full Product List </h2>
                <!--will be able to filter catalog with keywords-->
                <input type="text" id="search" onkeyup="myFunction()" placeholder="Search">

                <div id="table-scroll">
                    <table>
                        <?php
                            /*access data from legacy database*/
                            $sql = $legacyDB->prepare("SELECT * FROM parts;");
                            if(!$sql) {

                                die("Error with SQL Query: " . $legacyDB->error);
                            }

                            /*loop to display catalog items in a table*/
                            $sql->execute();
                            while($row=$sql->fetch(PDO::FETCH_ASSOC)) {
                        ?>

                        <tr>
                           <td> <img src='<?php echo $row['pictureURL'];?>'></td>
                           <td> <?php echo $row['description']; ?> <br>
                           $<?php echo $row['price']; ?> <br>
                           <?php echo $row['weight'];?> lbs <br>
                           <td>
                             <!--each row has an 'Add to Cart' button for its corresponding item-->
                             <form action="main_page.php">
                                <label for="quantity">Quantity:</label>
                                <input type="text" id="quantity" value="0" style="width:30px">
                                <input type="submit" value="Add to Cart">
                             </form>
                           </td>
                        </tr>
                        <?php
                           }
                        ?>
                    </table>
                </div>
            </div>
        </main>
    </body>
</html>
