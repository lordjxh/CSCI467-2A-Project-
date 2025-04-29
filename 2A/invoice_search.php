<?php
    /*Group 2A - CSCI 467 Spring 2025
      invoice_search.php - page that an administrator will use to view all invoices. Admin
          can filter through the invoices by date range, price, and order status. They can
          view the details of an order in this table as well */

    session_start();

    /*includes necessary functions*/
    include "secrets.php";
    include "php_functions/database_functions.php";
    include "php_functions/user_functions.php";

    /*establishes connections to legacy database and current database*/
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);

    /*if the user is not logged in as an admin, redirect to admin login*/
    if($_SESSION['isAdmin'] == false)
    {
        header("Location: esignon_page.php");
        exit();
    }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/invoice_search.css">
        <title>Invoices</title>
    </head>
    <body>
        <header>
            <nav>
                <a href="admin_page.php" class="button"><- Go Back</a>
            </nav>
        </header>
        <main>
            <h2> Filter Search of Orders </h2>
            <hr>
            <!--This form takes user input to filter by date, price, and status-->
            <form method="GET" action="invoice_search.php">
                <!--Filter by Date-->
                <h3>Order Date</h3>
                <label for="startDate">From:</label>
                <input type="date" id="startDate" name="startDate">
                <label for="endDate">To:</label>
                <input type="date" id="endDate" name="endDate">

                <!--Dropdown selection to filter by status-->
                <h3>Order Status</h3>
                <select name="status" id="status">
                    <option value="all">All</option>
                    <option value="shipped">Shipped</option>
                    <option value="authorized">Authorized</option>
                </select>

                <!--Price-->
                <h3>Price Range</h3>
                <label for="startPrice">From:</label>
                <input type="number" id="startPrice" name="startPrice" value="0">
                <label for="endPrice">To:</label>
                <input type="number" id="endPrice" name="endPrice" value="1000000">

                <!--Button to apply the filter-->
                <br>
                <button type="submit">Apply</button>
                <hr>
            </form>
            <!--Handles the "Details" button and displays invoice details-->
            <?php
                if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['details'])) {
                    $invoiceID = $_POST['invoiceID'] ?? null;

                    /*connects to InvoiceDB table to access order details*/
                    $invoiceQuery = $database->prepare("SELECT * FROM InvoiceDB WHERE invoiceNO = " . $invoiceID);
                    $invoiceQuery->execute();
                    $iRow=$invoiceQuery->fetch(PDO::FETCH_ASSOC);

                    /*connects to Purchases table to access quantities and product ID's*/
                    $purchaseQuery = $database->prepare("SELECT * FROM Purchases WHERE invoiceNO = " . $invoiceID);
                    $purchaseQuery->execute();

                    /*connects to ShippingInfo table to display customer/order information*/
                    $shippingQuery = $database->prepare("SELECT * FROM ShippingInfo WHERE invoiceNO = " . $invoiceID);
                    $shippingQuery->execute();
                    $sRow=$shippingQuery->fetch(PDO::FETCH_ASSOC);

                    /*Invoice is divided into 1) Order Details 2) Item Details and 3) Payment Details*/
                    echo "<div class='parent'>";
                    echo "<div class='child'><h2>Order Details:</h2>";
                    echo "Order #" . $invoiceID . "<br>";
                    echo "Status: " . $iRow['fulfillmentStatus'] . "<br>";
                    echo "Order placed: " . $iRow['datePaid'] . "<br><br>";
                    echo $sRow['shippingEmail'] . "<br>";
                    echo $sRow['shippingPhone'] . "<br>";
                    echo $sRow['shippingFirstName'] . " " . $sRow['shippingLastName'] . "<br>";
                    echo $sRow['shippingAddress'] . "<br>";
                    echo $sRow['shippingCity'] . ", " . $sRow['shippingState'] . " " . $sRow['shippingZipcode'] . "</div><br>";

                    echo "<div class='child'><h2>Item Details:</h2>";
                    while($pRow=$purchaseQuery->fetch(PDO::FETCH_ASSOC)) {
                        $product = $pRow['productID'];

                        /*the productID is found in the Purchases table and is compared with
                          its corresponding legacy id in the Products table*/
                        $productQuery = $database->prepare("SELECT legacyID FROM Products WHERE productID = " . $product);
                        $productQuery->execute();
                        $pResult=$productQuery->fetch(PDO::FETCH_ASSOC);

                        $legacyID = $pResult['legacyID'];

                        /*then the legacyID is used to find the description and price of an item
                          to display in the invoice*/
                        $legacyQuery = $legacyDB->prepare("SELECT description, price FROM parts WHERE number = " . $legacyID);
                        $legacyQuery->execute();
                        $lRow=$legacyQuery->fetch(PDO::FETCH_ASSOC);

                        echo $pRow['quantity'] . "x ";
                        echo $lRow['description'] . " ";
                        echo " ($" . $lRow['price'] . ")<br>";
                    }
                    echo "</div>";
                    echo "<div class='child'><h2>Payment Details:</h2>";
                    echo "Subtotal: $" . $iRow['subtotal'] . "<br>";
                    echo "Shipping: $" . $iRow['shippingCost'] . "<br>";
                    echo "Total: $". $iRow['grandTotal'] . "<br></div>";

                 echo "</div>";
                }
            ?>
            <hr>
            <div id="table-scroll">
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Status</th>
                        <th>Date Placed</th>
                        <th>Total Price</th>
                        <th>View Invoice</th>
                    </tr>
                    <?php
                        /*by default, sql query selects all invoices from the db*/
                        $sql = "SELECT * FROM InvoiceDB WHERE 1=1";
                        $values = [];

                        /*adds conditions to the sql statement if the date range is set*/
                        if(!empty($_GET['startDate']) && !empty($_GET['endDate'])) {
                            $sql .= " AND datePaid BETWEEN :startDate AND :endDate";
                            $values['startDate'] = $_GET['startDate'] . ' 00:00:00';
                            $values['endDate'] = $_GET['endDate'] . ' 23:59:59';
                        }

                        /*adds condition to the sql statement if the status filter is set*/
                        if(isset($_GET['status']) && $_GET['status'] !== 'all') {
                            if($_GET['status'] == "shipped") {
                                $sql .= " AND fulfillmentStatus = 'Y'";
                            } else {
                                $sql .= " AND fulfillmentStatus = 'N'";
                            }
                        }

                        /*adds condition to the sql statement if the price range is set*/
                        if(isset($_GET['startPrice']) && !empty($_GET['endPrice'])) {
                            $sql .= " AND grandTotal BETWEEN " . $_GET['startPrice'] . " AND " . $_GET['endPrice'];
                        }

                        $query = $database->prepare($sql);
                        /*binds filter values to the query to be executed*/
                        foreach($values as $key => $value) {
                            $query->bindValue(":$key", $value);
                        }

                        $query->execute();
                        /*displays the table of invoices based on updated query*/
                        while($row=$query->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td> <?php echo $row['invoiceNO']; ?> </td>
                        <td> <?php echo $row['fulfillmentStatus']; ?> </td>
                        <td> <?php echo $row['datePaid']; ?> </td>
                        <td> $<?php echo $row['grandTotal']; ?> </td>
                        <td>
                            <!--This form provides a button for the admin to view the details of a selected invoice-->
                            <form action="invoice_search.php" method="POST">
                                <input type="hidden" name="invoiceID" value="<?php echo $row['invoiceNO']; ?>">
                                <input type="submit" id="details" name="details" value="Details" style="width:60px">
                            </form>
                        </td>
                    </tr>
                    <?php
                        }
                    ?>
                </table>
            </div>
        </main>
    </body>
</html>
