<?php

function processInvoice($userID, $cartItems, $database)
{
    global $trans;  //used in ccValidation.php

    $subtotal = getSubtotal($cartItems);
    $shipping = getShippingCost($cartItems, $database);
    $totalCost = ($subtotal + $shipping);

    if($userID == NULL)
    {
        $statement = "INSERT INTO InvoiceDB(subtotal, shippingCost, grandTotal, datePaid, authorizationNO, fulfillmentStatus, shippingFlag) VALUES(" . 
            $subtotal . ", " . $shipping . ", " . $totalCost . ", '" . date("Y-m-d H:m:s") . "', " . $trans . ", 'N', 'N');";
    }
    else
    {
        $statement = "INSERT INTO InvoiceDB(userID, subtotal, shippingCost, grandTotal, datePaid, authorizationNO, fulfillmentStatus, shippingFlag) VALUES(" . 
            $userID . ", " . $subtotal . ", " . $shipping . ", " . $totalCost . ", '" . date("Y-m-d H:m:s") . "', " . $trans . ", 'N', 'N');";
    }

    insertDatabaseValue($database, $statement);
    
    $invoiceID = $database->lastInsertID();
    
    echo $invoiceID;

    return $invoiceID;
}

//processShipping() - takes user input and stores into the ShippingInfo table
//inputs -
    //$invoiceID - the id of the invoice to store shipping for, required to create entry
    //$database - the database $pdo initialized
//output - inserts value into ShippingInfo table
function processShipping($invoiceID, $database)
{
    $shippingFirstName = $_POST['firstName'];
    $shippingLastName = $_POST['lastName'];
    $shippingAddr = $_POST['address'];
    $shippingCity = $_POST['city'];
    $shippingState = $_POST['state'];
    $shippingZipcode = $_POST['zipcode'];
    $shippingEmail = $_POST['email'];
    $shippingPhone = $_POST['phone'];

    $statement = "INSERT INTO ShippingInfo(invoiceNO, shippingFirstName, shippingLastName, shippingAddress, shippingCity, " . 
        "shippingState, shippingZipcode, shippingEmail, shippingPhone) VALUES (" . $invoiceID . ", '" . $shippingFirstName . "', '" . 
        $shippingLastName . "', '" . $shippingAddr . "', '" . $shippingCity . "', '" . $shippingState . "', " . $shippingZipcode . ", '" . 
        $shippingEmail . "', " . $shippingPhone . ");";

    insertDatabaseValue($database, $statement);
}

//processBilling() - takes user input and stores into the BillingInfo table
//inputs -
    //$invoiceID - the id of the invoice to store shipping for, required to create entry
    //$database - the database $pdo initialized
//output - inserts value into BillingInfo table
function processBilling($invoiceID, $useShipping, $database)
{
    //if $useShipping is true, shipping info is used instead of billing info.
    //this is already done in checkout.php, but used here as a safeguard
    if($useShipping == true)
    {
        $billingFirstName = $_POST['firstName'];
        $billingLastName = $_POST['lastName'];
        $billingAddr = $_POST['address'];
        $billingCity = $_POST['city'];
        $billingState = $_POST['state'];
        $billingZipcode = $_POST['zipcode'];
        $billingEmail = $_POST['email'];
        $billingPhone = $_POST['phone'];
    }
    else
    {
        $billingFirstName = $_POST['billingFirstName'];
        $billingLastName = $_POST['billingLastName'];
        $billingAddr = $_POST['billingAddress'];
        $billingCity = $_POST['billingCity'];
        $billingState = $_POST['billingState'];
        $billingZipcode = $_POST['billingZipcode'];
        $billingEmail = $_POST['billingEmail'];
        $billingPhone = $_POST['billingPhone'];
    }

    $statement = "INSERT INTO BillingInfo(invoiceNO, billingFirstName, billingLastName, billingAddress, billingCity, " . 
        "billingState, billingZipcode, billingEmail, billingPhone) VALUES (" . $invoiceID . ", '" . $billingFirstName . "', '" . 
        $billingLastName . "', '" . $billingAddr . "', '" . $billingCity . "', '" . $billingState . "', " . $billingZipcode . ", '" . 
        $billingEmail . "', " . $billingPhone . ");";

    insertDatabaseValue($database, $statement);
}

//processPurchases() - migrates a user's cart items into the Purchases table, and ties to the Invoice ID
//inputs -
    //$userID - the id of the user to retrieve then remove items from the cart
    //$isAccount - boolean determines if the user has an account, which changes SQL statement to pull from
    //$invoiceID - the id of the invoice to store shipping for, required to create entry
    //$database - the database $pdo initialized
//output - inserts value into BillingInfo table
function processPurchases($userID, $isAccount, $invoiceID, $database)
{
    //Step 1
    //migrate CustomerCart values to Purchases

    if($isAccount == true)
    {
        $statement = "SELECT productID, quantity FROM CustomerCart WHERE userAccID = " . $userID . ";";
    }
    else
    {
        $statement = "SELECT productID, quantity FROM CustomerCart WHERE userID = " . $userID . ";";
    }

    $rs = getSQL($database, $statement);

    foreach($rs as $value)
    {
        //Step 2
        //Store extracted values then insert into Purchases table

        $productID = $value['productID'];
        $quantity = $value['quantity'];

        $statement2 = "INSERT INTO Purchases(InvoiceNO, productID, quantity) VALUES (" . $invoiceID . ", " . $productID .
            ", " . $quantity . ");";

        insertDatabaseValue($database, $statement2);

        //Step 3
        //Update the Products table to reduce the online quantity

        $statement3 = "SELECT storeQuantity FROM Products WHERE productID = " . $productID . ";";
        $rs2 = getSQL($database, $statement3);

        $oldValue = extractSingleValue($rs2);
        $newValue = $oldValue - $quantity;

        $statement4 = "UPDATE Products SET storeQuantity = " . $newValue . " WHERE productID = " . $productID . ";";

        updateDatabaseValue($database, $statement4);
    }

    //Step 4
    //remove all cart values for the associated userID

    if($isAccount == true)
    {
        $statement5 = "DELETE FROM CustomerCart WHERE userAccID = " . $userID . ";";
    }
    else
    {
        $statement5 = "DELETE FROM CustomerCart WHERE userID = " . $userID . ";";
    }

    deleteDatabaseValue($database, $statement5);
}

function storeUserCard()
{
    //implement later
}
?>