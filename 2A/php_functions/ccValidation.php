<?php
//This file handles the credit card validation during the checkout process. It requires all form values to be filled.

$vendor = "2A-CORP";
$trans = NULL;
$trans_formatted = NULL;
$cc = $_POST['cardNumber'];
$name = $_POST['billingFirstName'] . " " . $_POST['billingLastName'];
$exp = $_POST['cardMonth'] . "/" . $_POST['cardYear'];
$amount = NULL;

function validateCC() {
    global $vendor, $trans_formatted, $cc, $name, $exp, $amount;

    $url = 'http://blitz.cs.niu.edu/CreditCard/';
    $data = array(
	    'vendor' => $vendor,
	    'trans' => $trans_formatted,
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

function validateCheckout()
{
    global $vendor, $trans, $trans_formatted, $cc, $name, $exp, $amount;

    //create a random transaction number
    $trans_sec1 = rand(1000, 9999);
    $trans_sec2 = rand(1000, 9999);

    $trans = $trans_sec1 . $trans_sec2;
    $trans_formatted = $trans_sec1 . "-" . $trans_sec2;

    //DEBUG - confirm no values are blank before proceeding with credit card validation
    echo "Check these variables:";
    echo $vendor . " ";
    echo $trans;
    echo $trans_formatted . " ";
    echo $cc . " ";
    echo $name . " ";
    echo $exp . " ";
    echo $amount;

    return false;
}
?>