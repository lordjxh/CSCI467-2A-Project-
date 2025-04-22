<?php
//printCart() - prints a table of a user's current cart by extracting data from the current and legacy databases
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
    //$database - the new database that holds new attributes
    //$fullOutput - a boolean to tell whether to print all aspects of the cart (column headers, price by item)
//output - HTML table of a user's shopping cart
function printCart($cartItems, $database, $fullOutput)
{
    echo "<table class=\"cart\">";

    if($fullOutput == true)
    {
        echo "<tr><td></td><td></td><td class=\"cart-header\">Quantity</td><td></td><td class=\"cart-header\">Price</td></tr>";
    }

    foreach($cartItems as $item)
    {
        $productID = $item['productID'];
        $pictureURL = $item['pictureURL'];
        $description = $item['description'];
        $quantity = $item['quantity'];
        $price = ($item['price'] * $quantity);

        if($item['inStock'] == true)
        {
            echo "<tr class=\"cart-item\" data-id=\"" . $productID . "\">";
        }
        else
        {
            echo "<tr class=\"invalid-item\" data-id=\"" . $productID . "\">";
        }

        echo "<td class=\"item-image\"><img src=\"" . $pictureURL . "\" /></td>";
        echo "<td class=\"item-description\">" . $description . "</td>";
        echo "<td class=\"item-quantity\">" . $quantity . "</td>";

        if($fullOutput == true) //handles quantity buttons
        {
            echo "<td class=\"quantity-buttons\">";

            echo "<form method=\"post\">";
            echo "<input type=\"hidden\" name=\" productID\" value=" . $productID . ">";
            echo "<input type=\"hidden\" name=\" quantity\" value=" . $quantity . ">";
            echo "<button class=\"quantity-button\" id=\"decrease-quantity\" type=\"submit\" name=\"decrease\">-</button>";
            echo "<button class=\"quantity-button\" id=\"increase-quantity\" type=\"submit\" name=\"increase\">+</button>";
            echo "<button class=\"quantity-button\" id=\"remove-item\" type=\"submit\" name=\"remove\">x</button>";
            echo "</form>";
            echo "</td>";
    
            echo "<td class=\"item-price\">$" . number_format($price, 2) . "</td>";
        }

        echo "</tr>";
    }

    $subtotal = getSubtotal($cartItems);
    $shipping = getShippingCost($cartItems, $database);
    $total = ($subtotal + $shipping);

    //handles subtotal, shipping, and total cost, requires $database
    if($fullOutput == true) //for cart page
    {
        echo "<tr><td/><td/><td/><td class=\"cost-label\">Subtotal:</td><td class=\"cost-sum\">$" . number_format($subtotal, 2) . "</td></tr>";
        echo "<tr><td/><td/><td/><td class=\"cost-label\">Shipping:</td><td class=\"cost-sum\">$" . number_format($shipping, 2) . "</td></tr>";
        echo "<tr><td/><td/><td/><td class=\"cost-label\">Total:</td><td class=\"cost-sum\">$" . number_format($total, 2) . "</td></tr>";
    }
    else //for order summary pages (i.e. checkout, invoice lookup) (has alternate spacing)
    {
        echo "<tr><td/><td class=\"cost-label\">Subtotal:</td><td class=\"cost-sum\">$" . number_format($subtotal, 2) . "</td></tr>";
        echo "<tr><td/><td class=\"cost-label\">Shipping:</td><td class=\"cost-sum\">$" . number_format($shipping, 2) . "</td></tr>";
        echo "<tr><td/><td class=\"cost-label\">Total:</td><td class=\"cost-sum\">$" . number_format($total, 2) . "</td></tr>";
    }

    echo "</table>";
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

//isCartEmpty() - simple function that returns boolean if at least one item is found in cart array
//returned from getCartContents();
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
//output - boolean; true if at least one item, false otherwise
function isCartEmpty($cartItems)
{
    foreach($cartItems as $item) //if at least one item exists, function will return false
    {
        return false;
    }

    return true;
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