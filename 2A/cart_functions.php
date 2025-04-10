<?php
//printCart() - prints a table of a user's current cart by extracting data from the current and legacy databases
//inputs -
    //$rs - the array of queried content(s) before separation by row
    //$currentDB - the new database that holds new attributes
    //$legacyDB - the legacy database to retrive items from
//output - HTML table of a user's shopping cart
function printCart($rs, $database, $legacyDB)
{
    $subtotal = 0; //variable to hold the subtotal of the cart

    echo "<table>";
    echo "<tr><td></td><td></td><td>Quantity</td><td>Price</td></tr>";

    //extract the productID from the query, then query the legacyDB for details
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $productID = $row['productID'];
        $quantity = $row['quantity'];

        //get the legacyID from the Products table
        $statement = "SELECT legacyID FROM Products WHERE ProductID = " . $productID . ";";
        $rs2 = getSQL($database, $statement);
        $legacyID = extractSingleValue($rs2);

        //get legacyDB values from parts table
        $statement = "SELECT pictureURL, description, price FROM parts WHERE number = " . $legacyID . ";";
        $rs3 = getSQL($legacyDB, $statement);

        //navigate through rows to print & store values
        while($row2 = $rs3->fetch(PDO::FETCH_ASSOC))
        {
            $description = $row2['description'];
            $pictureURL = $row2['pictureURL'];
            $price = $row2['price'];

            $subtotal += ($price * $quantity);
            
            echo "<tr>";
            echo "<td><img src=\"" . $pictureURL . "\"></img></td>";
            echo "<td>" . $description . "</td>";
            
            cartQuantity($database, $productID, $quantity);
            
            echo "<td>" . $price . "</td>";
            echo "</tr>";
        }
    }

    //output subtotal, then close table
    echo "<tr><td/><td/><td/><td>" . $subtotal . "</td></tr>";
    echo "</table>";
}

//cartQuantity() - handles the quantity of an item in the cart
function cartQuantity($database, $productID, $cartQuantity)
{
    $statement = "SELECT quantity FROM Products WHERE legacyID = " . $productID . ";";
    $rs = getSQL($database, $statement);
    $inventoryQuantity = extractSingleValue($rs);

    if($cartQuantity > $inventoryQuantity)
    {
        echo "<td>ERROR: NOT ENOUGH INVENTORY FOR ITEM</td>";
    }

    echo "<td>" . $cartQuantity;

    echo "<form method=\"post\">";
    echo "<input type=\"hidden\" name=\"productID\" value=" . $productID . ">";
    echo "<input type=\"hidden\" name=\"quantity\" value=" . $cartQuantity . ">";
    echo "<button type=\"submit\" name=\"decrease\">-</button>";
    echo "<button type=\"submit\" name=\"increase\">+</button>";
    echo "<button type=\"submit\" name=\"remove\">x</button>";
    echo "</form>";

    echo "</td>";
}
?>