<?php

function processInvoice($database)
{
    $subtotal = $_POST['subtotal'];
    $shipping = $_POST['shippingCost'];
    $totalCost = $subtotal + $shipping;

    $statement = "INSERT INTO InvoiceDB(subtotal, shippingCost, grandTotal, datePaid, authorizationNO, fulfillmentStatus, shippingFlag)
        VALUES(";
}

//works
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

function processBilling($invoiceID, $database)
{

}

function processPurchases($userID, $invoiceID, $database)
{
    
}
?>