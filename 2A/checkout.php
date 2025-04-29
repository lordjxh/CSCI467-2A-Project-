<!-- 
    Group 2A - CSCI 467 Spring 2025
    checkout.php - The front-end for the checkout process. Should only be accessed if a cart is found and has valid quantities. Allows users to
        enter their shipping and billing information, as well as payment info. Calls a CC API to validate payment information, then begins to
        populate respective tables of information while migrating items in 'CustomerCart' to 'Purchases' for long-term storage. Upon successful
        checkout, redirects the user to checkout_confirmation.php with a valid invoice number for later reference.

-->

<?php
    include "secrets.php";
    include "php_functions/database_functions.php";
    include "php_functions/ccValidation.php";
    include "php_functions/cart_functions.php";
    include "php_functions/checkout_functions.php";

    session_start();

    //establish connection(s) to database(s)
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    //this page should not be accessed without a userID. Stop page if no userID is found in $_SESSION
    if($_SESSION['userID'] == NULL)
    {
        http_response_code(401);
        die("This page should not have been reached without a valid userID");
    }

    //ensure that $_SESSION['logged_on'] is set before proceeding
    if($_SESSION['logged_in'] != true)
    {
        if($_SESSION['logged_in'] != false)
        {
            http_response_code(400);
            die("Failed to determine account status.");
        }
    }

    //force retrieving the cart again (ensures quantity does not change during checkout)
    if($_SESSION['logged_in'] == true) //if the user is logged in, query by UserAccID
    {
        $cartQuery = "SELECT * FROM CustomerCart WHERE UserAccID = " . $_SESSION['userID'] . ";";
    }
    else //else query by UserID
    {
        $cartQuery = "SELECT * FROM CustomerCart WHERE UserID = " . $_SESSION['userID'] . ";";
    }

    $rs = getSQL($database, $cartQuery);
    $cartItems = getCartContents($rs, $database, $legacyDB);
    $_SESSION['cart'] = $cartItems;

    //confirm the cart is not empty
    if(isCartEmpty($_SESSION['cart']) == true)
    {
        http_response_code(403);
        die("The user's cart is empty. This page should not be accessible with zero items.");
    }

    //confirm the store front's available quantity did not change during checkout, otherwise redirect back to cart.php
    if(confirmValidQuantity($_SESSION['cart']) == false)
    {
        header("Refresh: 5; URL=cart.php");
        echo "One or more items in your cart are no longer available.</br>Redirecting to cart in 5 seconds...";
        exit();
    }

    //retrieve subtotal and shipping amounts for processing
    $subtotal = getSubtotal($_SESSION['cart']);
    $shipping = getShippingCost($_SESSION['cart'], $database);

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $valid = validateCheckout();

        if(isset($valid['errors']))
        {
            $printErrors = true;
        }
        else
        {
            //create invoice, retrieve invoiceID from new value inserted
            $invoiceID = processInvoice($_SESSION['userID'], $_SESSION['logged_in'], $_SESSION['cart'], $database);

            //process user input for order into shipping and billing info tables
            processShipping($invoiceID, $database);
            processBilling($invoiceID, $_POST['matchShipping'], $database);
            
            //move purchased items from CustomerCart to Purchases table, and update inventory
            processPurchases($_SESSION['userID'], $_SESSION['logged_in'], $invoiceID, $database);
            
            //store invoiceID into $_SESSION variable for usage on next page
            $_SESSION['invoiceID'] = $invoiceID;

            //navigate to checkout_confirmation upon reaching this point successfully
            header("Location: checkout_confirmation.php");
            exit();
        }
    }
?>

<!-- Start of HTML Block -->

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/checkout.css">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <title> Checkout </title>
        <script> var currentTab = 0; </script>
    </head>
    <body>
        <div class="container">        
            <!-- Form Entry (Left Side) -->
            <div class="left">
                <div class="section">
                    <form id="regForm" method="post">
                        <div class="tab"> <!-- Customer Info and Shipping Info -->
                            <h1>Contact & Shipping</h1>
                            
                            <h2>Name</h2>
                            <p><input id="firstName" name="firstName" placeholder="First name..." maxlength="32" value="<?php echo $_POST['firstName']; ?>" oninput="this.className = ''"></p>
                            <p><input id="lastName" name="lastName" placeholder="Last name..." maxlength="32" value="<?php echo $_POST['lastName']; ?>" oninput="this.className = ''"></p>
                            
                            <h2>Address</h2>
                            <p><input id="address" name="address" placeholder="Address..." maxlength="64" value="<?php echo $_POST['address']; ?>" oninput="this.className = ''"></p>
                            <p><input id="city" name="city" placeholder="City..." maxlength="48" value="<?php echo $_POST['city']; ?>" oninput="this.className = ''"></p>
                            <p><input id="state" name="state" placeholder="State..." maxlength="2" value="<?php echo $_POST['state']; ?>" oninput="this.className = ''"></p>
                            <p><input id="zipcode" name="zipcode" placeholder="Zipcode..." maxlength="10" value="<?php echo $_POST['zipcode']; ?>" oninput="this.className = ''"></p>
                            
                            <h2>Contact Info</h2>
                            <p><input type="email" id="email" name="email" placeholder="Email..." maxlength="32" value="<?php echo $_POST['email']; ?>" oninput="this.className = ''"></p>
                            <p><input type="tel" id="phone" name="phone" placeholder="Phone..." maxlength="14" value="<?php echo $_POST['phone']; ?>" oninput="this.className = ''"></p>
                            <div id="shippingError" style="color: red; font-size: 0.9em; margin-top: 4px;"></div>
                        </div>

                        <div class="tab"> <!-- Billing Address -->
                            <h2>Billing Address</h2>
                            <p><input id="matchShipping" name="matchShipping" type="checkbox">Same as Shipping</p>
                            <p><input id="billingFirstName" name="billingFirstName" maxlength="32" placeholder="First name..." value="<?php echo $_POST['billingFirstName']; ?>" oninput="this.className = ''"></p>
                            <p><input id="billingLastName" name="billingLastName" maxlength="32" placeholder="Last name..." value="<?php echo $_POST['billingLastName']; ?>" oninput="this.className = ''"></p>
                            <p><input id="billingAddress" name="billingAddress" maxlength="64" placeholder="Address..." value="<?php echo $_POST['billingAddress']; ?>" oninput="this.className = ''"></p>
                            <p><input id="billingCity" name="billingCity" maxlength="48" placeholder="City..." value="<?php echo $_POST['billingCity']; ?>" oninput="this.className = ''"></p>
                            <p><input id="billingState" name="billingState" maxlength="2" placeholder="State..." value="<?php echo $_POST['billingState']; ?>" oninput="this.className = ''"></p>
                            <p><input id="billingZipcode" name="billingZipcode" maxlength="10" placeholder="Zipcode..." value="<?php echo $_POST['billingZipcode']; ?>" oninput="this.className = ''"></p>
                            
                            <h2>Billing Contact Info</h2>
                            <p><input type="email" id="billingEmail" name="billingEmail" placeholder="Email..." maxlength="32" value="<?php echo $_POST['billingEmail']; ?>" oninput="this.className = ''"></p>
                            <p><input type="tel" id="billingPhone" name="billingPhone" placeholder="Phone..." maxlength="14" value="<?php echo $_POST['billingPhone']; ?>" oninput="this.className = ''"></p>
                            <div id="billingError" style="color: red; font-size: 0.9em; margin-top: 4px;"></div>
                        </div>

                        <div class="tab"> <!-- Payment Info -->
                            <h2>Payment Info</h2>
                            <p><input id="cardNumber" name="cardNumber" placeholder="Card Number..." maxlength="19" value="<?php echo $_POST['cardNumber']; ?>" oninput="this.className = ''"></p>
                            <p><input id="cardMonth" name="cardMonth" placeholder="MM..." maxlength="2" value="<?php echo $_POST['cardMonth']; ?>" oninput="this.className = ''"></p>
                            <p><input id="cardYear" name="cardYear" placeholder="YY..." maxlength="2" value="<?php echo $_POST['cardYear']; ?>" oninput="this.className = ''"></p>
                            <p><input id="cardSecurity" name="cardSecurity" placeholder="Security..." maxlength="4" value="<?php echo $_POST['cardSecurity']; ?>" oninput="this.className = ''"></p>
                            <div id="paymentError" style="color: red; font-size: 0.9em; margin-top: 4px;"></div>
                        </div>

                        <div class="tab"> <!-- Final Confirmation of Purchase -->
                            <h2>Order Details</h2>
                            <h3>Shipping</h3>
                            <p id="fullNameOutput"></p>
                            <p id="streetAddressOutput"></p>
                            <p id="fullAddressOutput"></p>
                            <p id="fullContactOutput"></p>

                            <h3>Billing</h3>
                            <p id="cardNumberOutput"></p>
                            <p id="cardNameOutput"></p>
                            <p id="billingAddressOutput"></p>
                            <p id="billingFullAddressOutput"></p>
                            <p id="billingFullContactOutput"></p>
                        </div>

                        <div style="overflow:auto;"> <!-- Previous/Next Buttons -->
                            <div style="float:right;">
                                <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                                <button type="button" id="nextBtn" onclick="nextPrev(1)">Next</button>
                            </div>
                        </div>

                        <div style="text-align:center;margin-top:40px;"> <!-- Navigation Bubbles -->
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
                            <span class="step"></span>
                        </div>

                        <input type="hidden" id="amount" name="amount" value="<?php echo ($subtotal + $shipping); ?>"/>
                    </form>
                </div>
            </div>

            <!-- Cart Summary (Right Side) -->
                <div class="right">
                    <div class="sub-container">
                        <?php
                            //if any errors occurred during checkout, print here
                            if($printErrors == true)
                            {
                                echo "<div class=\"section\">";
                                echo "<div class=\"errorMessage\">";
                                echo "<p class=\"errorMessage-header\">⚠️ Transaction failed with the following errors:</p>";

                                foreach($valid['errors'] as $error)
                                {
                                    //echo "<p>" . $error . "</p>";
                                    //echo "<p>Placeholder</p>";
                                }

                                echo "</div>";
                                echo "</div>";
                            }
                        ?>
                        <div class="section">
                            <?php
                                //print cart contents as summary view
                                if(isCartEmpty($_SESSION['cart']) == false)
                                {
                                    printCart($_SESSION['cart'], false);
                                    printTotals($_SESSION['cart'], $database);
                                }
                                else
                                {
                                    echo "<p>The cart is empty.</p>";
                                }

                                if ($_SERVER['REQUEST_METHOD'] == 'POST')
                                {
                                    echo "<script> currentTab = 3; </script>";
                                }
                            ?>
                        </div>
                    </div>
                </div>
        </div>
        <script src="js/checkout_script.js" defer></script>
    </body>
</html>