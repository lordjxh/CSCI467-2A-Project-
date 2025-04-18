<?php
    include "secrets.php";
    include "php_functions/database_functions.php";
    include "php_functions/ccValidation.php";
    include "php_functions/cart_functions.php";
    include "php_functions/checkout_functions.php";

    session_start();

    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    //need a way to determine if a user has an account or is a guest user

    $userID = $_SESSION['userID']; //it is assumed reaching this point the user should have a valid userID...

    if($_SESSION['cart'] == NULL) //if cart fails to pass, call it back again
    {
        $cartQuery = "SELECT * FROM CustomerCart WHERE UserAccID = " . $userID . ";";
        $rs = getSQL($database, $cartQuery);
        $output = getCartContents($rs, $database, $legacyDB);
        $_SESSION['cart'] = $output;
    }

    //retrieve subtotal and shipping amounts for processing
    $subtotal = getSubtotal($_SESSION['cart']);
    $shipping = getShippingCost($_SESSION['cart'], $database);

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        //$valid = validateCheckout();

        //if(isset($valid['errors']))
        //{
            //$printErrors = true;
        //}
        //else
        //{
            //create invoice, retrieve invoiceID from new value inserted
            $invoiceID = processInvoice($userID, $_SESSION['cart'], $database);

            //process user input for order into shipping and billing info tables
            processShipping($invoiceID, $database);
            processBilling($invoiceID, $_POST['matchShipping'], $database);
            
            //move purchased items from CustomerCart to Purchases table, and update inventory
            processPurchases($userID, true, $invoiceID, $database);
            
            //if($_POST['storeCard'] == true)
            //{
                //storeUserCard();
            //}
            
            //navigate to checkout_confirmation upon reaching this point successfully
            header("Location: checkout_confirmation.php");
            exit();
        //}
    }
?>

<html>
    <head>
        <link rel="stylesheet" href="css/checkout.css">
        <script> var currentTab = 0; </script>
    </head>
    <body>
        <!-- Form Entry (Left Side) -->
        <div class="split left">
        <form id="regForm" method="post">
            <div class="tab"> <!-- Customer Info and Shipping Info -->
                <h1>Contact & Shipping</h1>

                <h2>Name</h2>
                <p><input id="firstName" name="firstName" placeholder="First name..." value="<?php echo $_POST['firstName']; ?>" oninput="this.className = ''"></p>
                <p><input id="lastName" name="lastName" placeholder="Last name..." value="<?php echo $_POST['lastName']; ?>" oninput="this.className = ''"></p>

                <h2>Address</h2>
                <p><input id="address" name="address" placeholder="Address..." value="<?php echo $_POST['address']; ?>" oninput="this.className = ''"></p>
                <p><input id="city" name="city" placeholder="City..." value="<?php echo $_POST['city']; ?>" oninput="this.className = ''"></p>
                <p><input id="state" name="state" placeholder="State..." value="<?php echo $_POST['state']; ?>" oninput="this.className = ''"></p>
                <p><input id="zipcode" name="zipcode" placeholder="Zipcode..." value="<?php echo $_POST['zipcode']; ?>" oninput="this.className = ''"></p>

                <h2>Contact Info</h2>
                <p><input id="email" name="email" placeholder="Email..." value="<?php echo $_POST['email']; ?>" oninput="this.className = ''"></p>
                <p><input id="phone" name="phone" placeholder="Phone..." value="<?php echo $_POST['phone']; ?>" oninput="this.className = ''"></p>

            </div>

            <div class="tab"> <!-- Billing Address -->
                <h2>Billing Address</h2>
                <p><input id="matchShipping" name="matchShipping" type="checkbox">Same as Shipping</p>

                <p><input id="billingFirstName" name="billingFirstName" placeholder="First name..." value="<?php echo $_POST['billingFirstName']; ?>" oninput="this.className = ''"></p>
                <p><input id="billingLastName" name="billingLastName" placeholder="Last name..." value="<?php echo $_POST['billingLastName']; ?>" oninput="this.className = ''"></p>
                <p><input id="billingAddress" name="billingAddress" placeholder="Address..." value="<?php echo $_POST['billingAddress']; ?>" oninput="this.className = ''"></p>
                <p><input id="billingCity" name="billingCity" placeholder="City..." value="<?php echo $_POST['billingCity']; ?>" oninput="this.className = ''"></p>
                <p><input id="billingState" name="billingState" placeholder="State..." value="<?php echo $_POST['billingState']; ?>" oninput="this.className = ''"></p>
                <p><input id="billingZipcode" name="billingZipcode" placeholder="Zipcode..." value="<?php echo $_POST['billingZipcode']; ?>" oninput="this.className = ''"></p>

                <h2>Billing Contact Info</h2>
                <p><input id="billingEmail" name="billingEmail" placeholder="Email..." value="<?php echo $_POST['billingEmail']; ?>" oninput="this.className = ''"></p>
                <p><input id="billingPhone" name="billingPhone" placeholder="Phone..." value="<?php echo $_POST['billingPhone']; ?>" oninput="this.className = ''"></p>
            </div>

            <div class="tab"> <!-- Payment Info -->
                <h2>Payment Info</h2>
                <p><input id="cardNumber" name="cardNumber" placeholder="Card Number..." value="<?php echo $_POST['cardNumber']; ?>" oninput="this.className = ''"></p>
                <p><input id="cardMonth" name="cardMonth" placeholder="MM..." value="<?php echo $_POST['cardMonth']; ?>" oninput="this.className = ''"></p>
                <p><input id="cardYear" name="cardYear" placeholder="YY..." value="<?php echo $_POST['cardYear']; ?>" oninput="this.className = ''"></p>
                <p><input id="cardSecurity" name="cardSecurity" placeholder="Security..." value="<?php echo $_POST['cardSecurity']; ?>" oninput="this.className = ''"></p>
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
        <?php
            //if any errors occurred during checkout, print here
            if($printErrors == true)
            {
                foreach($valid['errors'] as $error)
                {
                    echo "<p>" . $error . "</p>";
                }
            }
        ?>
        </div>

        <!-- Cart Summary (Right Side) -->
        <?php
            //print cart contents as summary view
            echo "<div class=\"split right\">";
            printCart($_SESSION['cart'], $database, false);
            echo "</div>";

            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                echo "<script> currentTab = 3; </script>";
            }
        ?>

        <script src="js/checkout_script.js"></script>
    </body>
</html>