<?php
//Group 2A - CSCI467 Spring 2025
//cart_functions - a PHP file for all functions used for items in a user's cart. Primarily used for cart.php 
//and checkout.php, and requires database_functions.php as a dependency.


//printCart() - prints a table of a user's current cart by extracting data from the current and legacy databases. Can specify full output to
//include extra columns such as quantity buttons and item's total price.
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
    //$fullOutput - a boolean to tell whether to print all aspects of the cart (column headers, price by item)
//output - HTML table of a user's shopping cart
function printCart($cartItems, $fullOutput)
{
    echo "<div class=\"cart-scroll\">"; //used during checkout specifically
    echo "<table class=\"cart\">";

    if($fullOutput == true)
    {
        echo "<tr><td></td><td></td><td class=\"cart-header\">Quantity</td><td></td><td class=\"cart-header\">Price</td></tr>";
    }

    //handles each item in the cart, printing as a table row
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

    echo "</table>";
    echo "</div>"; //cart-scroll end
}

//printTotals() - prints subtotal, shipping amount, and the total cost of all items in the cart.
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
    //$database - the PDO object with a valid database connected
//output - HTML elements of a user's total amounts
function printTotals($cartItems, $database)
{
    $subtotal = getSubtotal($cartItems);
    $shipping = getShippingCost($cartItems, $database);
    $total = ($subtotal + $shipping);

    echo "<div class=\"total-summary\">";

    echo "<div class=\"sum-row\">";
    echo "<p class=\"cost-label\">Subtotal:</p>";
    echo "<p class=\"cost-sum\">$" . number_format($subtotal, 2) . "</p>";
    echo "</div>";

    echo "<div class=\"sum-row\">";
    echo "<p class=\"cost-label\">Shipping:</p>";
    echo "<p class=\"cost-sum\">$" . number_format($shipping, 2) . "</p>";
    echo "</div>";

    echo "<div class=\"sum-row\">";
    echo "<p class=\"cost-label\">Total:</p>";
    echo "<p class=\"cost-sum\">$" . number_format($total, 2) . "</p>";
    echo "</div>";

    echo "</div>";
}

//getCartContents() - should be called before any other functions, takes a query prepared from CustomerCart,
//and extracts its contents from comparing the item with the legacyID, creating an array used by this file's functions
//inputs -
    //$rs - the array of queried content(s) before separation by row
    //$database - the PDO object with a valid database connected
    //$legacyDB - a second PDO object with a valid connection to the legacy database
//output - $cartItems - an array that contains information about product(s) a user has added to their cart
function getCartContents($rs, $database, $legacyDB)
{
    $index = 0;
    $cartItems = NULL;

    //extract the productID from the query, then query the legacyDB for details
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $productID = $row['productID'];
        $quantity = $row['quantity'];

        $cartItems[$index]['quantity'] = $row['quantity'];
        $cartItems[$index]['productID'] = $row['productID'];

        //get the legacyID from the Products table
        $statement = "SELECT legacyID FROM Products WHERE ProductID = " . $productID . ";";
        $rs2 = getSQL($database, $statement);
        $legacyID = extractSingleValue($rs2);

        //get legacyDB values from parts table
        $statement = "SELECT * FROM parts WHERE number = " . $legacyID . ";";
        $rs3 = getSQL($legacyDB, $statement);

        //navigate through rows to print & store values
        $row2 = $rs3->fetch(PDO::FETCH_ASSOC);
            
        $cartItems[$index]['description'] = $row2['description'];
        $cartItems[$index]['pictureURL'] = $row2['pictureURL'];
        $cartItems[$index]['price'] = $row2['price'];
        $cartItems[$index]['weight'] = $row2['weight'];
        
        $cartItems[$index]['inStock'] = isValidQuantity($database, $productID, $quantity);

        $index++;
    }

    return $cartItems;
}

//isCartEmpty() - simple function that returns boolean if at least one item is found in cart array
//returned from getCartContents();
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
//output - boolean - true if at least one item, false otherwise
function isCartEmpty($cartItems)
{
    foreach($cartItems as $item) //if at least one item exists, function will return false
    {
        return false;
    }

    return true;
}

//isValidQuantity() - handles the quantity of an item in the cart; returns boolean if the item's storeQuantity
//is greater than or equal to the user's cart quantity
//inputs -
    //$database - the PDO object with a valid database connected
    //$productID - the ID of the product (primary key of Products table)
    //$cartQuantity - a passed amount of the quantity specified in the user's cart
//output - boolean - true if amount is valid, false otherwise
function isValidQuantity($database, $productID, $cartQuantity)
{
    $statement = "SELECT storeQuantity FROM Products WHERE ProductID = " . $productID . ";";
    $rs = getSQL($database, $statement);
    $inventoryQuantity = extractSingleValue($rs);

    if($cartQuantity > $inventoryQuantity) //if the cart quantity is greater than the inventory, set false
    {
        return false;
    }

    return true;
}

//getTotalWeight() - used as part of getShippingCost(), will calculate the total weight of all items in
//the user's cart
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
//output - a double representing the total weight calculated
function getTotalWeight($cartItems)
{
    $totalWeight = 0;

    foreach($cartItems as $item) //for each item in cart, get the weight based on quantity
    {
        $totalWeight += $item['weight'] * $item['quantity'];
    }

    return $totalWeight;
}

//getSubtotal() - calculates the subtotal of the user's cart
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
//output - a double representing the subtotal calculated
function getSubtotal($cartItems)
{
    $subtotal = 0;

    foreach($cartItems as $item) //for each item in cart, extract quantity and price, then add to subtotal
    {
        $quantity = $item['quantity'];
        $price = $item['price'];

        $itemTotal = ($quantity * $price);
        $subtotal += $itemTotal;
    }

    return $subtotal;
}

//getShippingCost() - calculates the shipping cost of the user's cart. Based on the total weight of items, sets a
//percentage fetched from the ShippingWeights table and calculate based on the subtotal
//inputs -
    //$cartItems - the array of items in the user's cart called from getCartContents()
    //$database - the PDO object with a valid database connected
//output - a double representing the shipping cost calculated
function getShippingCost($cartItems, $database)
{
    $subtotal = getSubtotal($cartItems);
    $totalWeight = getTotalWeight($cartItems); 

    $shipping = 0;

    //fetch and extract the percentage amount to use
    $statement = "SELECT shippingPercent FROM ShippingWeights WHERE " . $totalWeight . " >= minimumWeight AND " . $totalWeight . " <= maximumWeight;";
    $rs = getSQL($database, $statement);

    $percentage = extractSingleValue($rs);

    //calculate the shipping amount with the percent of subtotal
    $shipping = ($subtotal * $percentage);
    $shipping = round($shipping, 2);

    return $shipping;
}
?>