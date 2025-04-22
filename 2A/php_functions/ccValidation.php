<?php
//This file handles the credit card validation during the checkout process. It requires all form values to be filled.

//Global variables. Several rely on $_POST from checkout.php to set.

$vendor = "2A-CORP";
$trans = NULL;
$trans_formatted = NULL;
$cc = $_POST['cardNumber'];
$name = NULL;
$exp = $_POST['cardMonth'] . "/" . "20" . $_POST['cardYear'];
$amount = $_POST['amount'];

//validateCC() - modified from project instructions, calls a card validation API to determine if the provided
//user card is valid.
//input(s) - none
//output - String of JSON pulled from API
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
    
    return $result;
}

//validateCheckout() - upon user checkout, this function takes user input, formats into an accepted input for
//the card validation API, and then calls validateCC() to get and decode JSON.
//input(s) - none; expects global variables to be set prior
//output - array of decoded JSON
function validateCheckout()
{
    global $trans, $trans_formatted, $cc, $name;

    //create a random transaction number
    $trans_sec1 = rand(1000, 9999);
    $trans_sec2 = rand(1000, 9999);

    //format transaction for API call
    $trans = $trans_sec1 . $trans_sec2;
    $trans_formatted = $trans_sec1 . "-" . $trans_sec2;

    //set name to use based on shipping or billing
    if($_POST['matchShipping'] == true)
    {
        $name = $_POST['firstName'] . " " . $_POST['lastName'];
    }
    else
    {
        $name = $_POST['billingFirstName'] . " " . $_POST['billingLastName'];
    }

    //debugPrintCCOutput();

    $result = validateCC();
    $result_decode = json_decode($result, true);

    return $result_decode;
}

//debugPrintCCOutput() - for debug purposes only, will print the contents of user input as it would
//be stored into the validateCC() function call to the card validation API
//input(s) - none
//output - prints to front-end
function debugPrintCCOutput()
{
    global $vendor, $trans, $trans_formatted, $cc, $name, $exp, $amount;

    echo "Check these variables:\n";
    echo $vendor . "\n";
    echo $trans_formatted . "\n";
    echo $cc . "\n";
    echo $name . "\n";
    echo $exp . "\n";
    echo $amount . "\n";
}
?>