<?php
//This file handles the credit card validation during the checkout process. It requires all form values to be filled.

$vendor = "2A-CORP";
$trans = NULL;
$cc = $_POST['cardNumber'];
$name = $_POST['cardName'];
$exp = $_POST['cardMonth'] . "/" . $_POST['cardYear'];
$amount = NULL;

function validateCC() {
    global $vendor, $trans, $cc, $name, $exp, $amount;

    $url = 'http://blitz.cs.niu.edu/CreditCard/';
    $data = array(
	    'vendor' => $vendor,
	    'trans' => $trans,
	    'cc' => $cc,
	    'name' => $name, 
	    'exp' => $exp, 
	    'amount' => $amount);

    $options = array(
        'http' => array(
            'header' => array('Content-type: application/json', 'Accept: application/json'),
            'method' => 'POST',
            'content'=> json_encode($data)
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    echo($result);
}

function validateCheckout($database)
{
    global $vendor, $trans, $cc, $name, $exp, $amount;

    //if a transaction was not generated, insert

    //DEBUG - confirm no values are blank before proceeding with credit card validation
    echo "Check these variables:";
    echo $vendor . " ";
    echo $trans . " ";
    echo $cc . " ";
    echo $name . " ";
    echo $exp . " ";
    echo $amount;

    return false;
}
?>