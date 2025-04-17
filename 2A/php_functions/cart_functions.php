<?php
//printCart() - prints a table of a user's current cart by extracting data from the current and legacy databases
//inputs -
    //$rs - the array of queried content(s) before separation by row
    //$currentDB - the new database that holds new attributes
    //$legacyDB - the legacy database to retrive items from
//output - HTML table of a user's shopping cart
function printCart($cartItems, $fullOutput)
{
    echo "<div class=\"cart\">";
    echo "<table>";

    if($fullOutput == true)
    {
        echo "<tr><td></td><td></td><td>Quantity</td><td>Price</td></tr>";
    }

    foreach($cartItems as $item)
    {
        $productID = $item['productID'];
        $pictureURL = $item['pictureURL'];
        $description = $item['description'];
        $quantity = $item['quantity'];
        $price = $item['price'];

        echo "<tr>";
        echo "<td><img src=\"" . $pictureURL . "\"></img></td>";
        echo "<td>" . $description . "</td>";

        echo "<td>" . $quantity;

        if($fullOutput == true)
        {
            echo "<form method=\"post\">";
            echo "<input type=\"hidden\" name=\"productID\" value=" . $productID . ">";
            echo "<input type=\"hidden\" name=\"quantity\" value=" . $quantity . ">";
            echo "<button type=\"submit\" name=\"decrease\">-</button>";
            echo "<button type=\"submit\" name=\"increase\">+</button>";
            echo "<button type=\"submit\" name=\"remove\">x</button>";
            echo "</form>";
    
            echo "</td>";
            echo "<td>" . $price . "</td>";

            if($item['inStock'] == false)
            {
                echo "<td>" . "ITEM NOT IN STOCK" . "</td>";
            }

        }
        echo "</tr>";
    }

    echo "</table>";
    echo "</div>";
}

function printTotal($cartItems, $database) //needs revision
{
    $subtotal = 0;
    $totalWeight = 0;

    foreach($cartItems as $item)
    {
        $price = $item['price'];
        $weight = $item['weight'];
    }
}

function getCartContents($rs, $database, $legacyDB)
{
    $index = 0;
    $output = NULL;

    //extract the productID from the query, then query the legacyDB for details
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $productID = $row['productID'];
        $quantity = $row['quantity'];

        $output[$index]['quantity'] = $row['quantity'];
        $output[$index]['productID'] = $row['productID'];

        //get the legacyID from the Products table
        $statement = "SELECT legacyID FROM Products WHERE ProductID = " . $productID . ";";
        $rs2 = getSQL($database, $statement);
        $legacyID = extractSingleValue($rs2);

        //get legacyDB values from parts table
        $statement = "SELECT * FROM parts WHERE number = " . $legacyID . ";";
        $rs3 = getSQL($legacyDB, $statement);

        //navigate through rows to print & store values
        $row2 = $rs3->fetch(PDO::FETCH_ASSOC);
            
        $output[$index]['description'] = $row2['description'];
        $output[$index]['pictureURL'] = $row2['pictureURL'];
        $output[$index]['price'] = $row2['price'];
        $output[$index]['weight'] = $row2['weight'];
        
        $output[$index]['inStock'] = isValidQuantity($database, $productID, $quantity);

        $index++;
    }

    return $output;
}

//isValidQuantity() - handles the quantity of an item in the cart
function isValidQuantity($database, $productID, $cartQuantity)
{
    $statement = "SELECT storeQuantity FROM Products WHERE ProductID = " . $productID . ";";
    $rs = getSQL($database, $statement);
    $inventoryQuantity = extractSingleValue($rs);

    if($cartQuantity > $inventoryQuantity)
    {
        return false;
    }

    return true;
}

function getTotalWeight($cartItems)
{
    $totalWeight = 0;

    foreach($cartItems as $item) //for each item in cart, get the weight based on quantity
    {
        $totalWeight += $item['weight'] * $item['quantity'];
    }

    return $totalWeight;
}

function getSubtotal($cartItems)
{
    $subtotal = 0;

    foreach($cartItems as $item)
    {
        $quantity = $item['quantity'];
        $price = $item['price'];

        $itemTotal = ($quantity * $price);
        $subtotal += $itemTotal;
    }

    return $subtotal;
}

function getShippingCost($cartItems, $database) //note - also forces totalCost to update
{
    $subtotal = getSubtotal($cartItems);
    $totalWeight = getTotalWeight($cartItems); 

    $shipping = 0;

    //fetch and extract the percentage amount to use
    $statement = "SELECT shippingPercent FROM ShippingWeights WHERE " . $totalWeight . " >= minimumWeight AND " . $totalWeight . " <= maximumWeight;";
    $rs = getSQL($database, $statement);

    $percentage = extractSingleValue($rs);

    $shipping = ($subtotal * $percentage);
    $shipping = round($shipping, 2);

    return $shipping;
}
?>