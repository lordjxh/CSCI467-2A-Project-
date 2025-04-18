<?php
//printCart() - prints a table of a user's current cart by extracting data from the current and legacy databases
//inputs -
    //$rs - the array of queried content(s) before separation by row
    //$currentDB - the new database that holds new attributes
    //$legacyDB - the legacy database to retrive items from
//output - HTML table of a user's shopping cart
function printCart($cartItems, $database, $fullOutput)
{
    echo "<div class=\"cart\">";
    echo "<table>";

    if($fullOutput == true)
    {
        echo "<tr><td></td><td></td><td>Quantity</td><td></td><td>Price</td></tr>";
    }

    foreach($cartItems as $item)
    {
        $productID = $item['productID'];
        $pictureURL = $item['pictureURL'];
        $description = $item['description'];
        $quantity = $item['quantity'];
        $price = ($item['price'] * $quantity);

        echo "<tr class=\"cart-item\" data-id=\"" . $productID . "\">";

        echo "<td><img src=\"" . $pictureURL . "\" /></td>";
        echo "<td>" . $description . "</td>";
        echo "<td>" . $quantity . "</td>";

        if($fullOutput == true) //handles quantity buttons
        {
            echo "<td class=\"quantity-buttons\">";

            echo "<form method=\"post\">";
            echo "<input type=\"hidden\" name=\" productID\" value=" . $productID . ">";
            echo "<input type=\"hidden\" name=\" quantity\" value=" . $quantity . ">";
            echo "<button id=\"decrease-quantity\" type=\"submit\" name=\"decrease\">-</button>";
            echo "<button id=\"increase-quantity\" type=\"submit\" name=\"increase\">+</button>";
            echo "<button id=\"remove-item\" type=\"submit\" name=\"remove\">x</button>";
            echo "</form>";
            echo "</td>";
    
            echo "<td>$" . $price . "</td>";

            if($item['inStock'] == false)
            {
                echo "<td>" . "ITEM NOT IN STOCK" . "</td>";
            }
        }

        echo "</tr>";
    }

    $subtotal = getSubtotal($cartItems);
    $shipping = getShippingCost($cartItems, $database);
    $total = ($subtotal + $shipping);

    //handles subtotal, shipping, and total cost, requires $database
    if($fullOutput == true) //for cart page
    {
        echo "<tr/><tr/><tr/>";
        echo "<tr><td>Subtotal:</td><td/><td/><td/><td>$" . $subtotal . "</td></tr>";
        echo "<tr><td>Shipping:</td><td/><td/><td/><td>$" . $shipping . "</td></tr>";
        echo "<tr><td>Total:</td><td/><td/><td/><td>$" . $total . "</td></tr>";
    }
    else //for order summary pages (i.e. checkout, invoice lookup)
    {
        echo "<tr/><tr/><tr/>";
        echo "<tr><td>Subtotal:</td><td/><td/><td>$" . $subtotal . "</td></tr>";
        echo "<tr><td>Shipping:</td><td/><td/><td>$" . $shipping . "</td></tr>";
        echo "<tr><td>Total:   </td><td/><td/><td>$" . $total . "</td></tr>";
    }

    echo "</table>";
    echo "</div>"; //div-cart
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

function getShippingCost($cartItems, $database)
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