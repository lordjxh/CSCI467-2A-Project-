<html>
    <head>
        <link rel="stylesheet" href="css/checkout.css">
        <?php
            include "database_functions.php";
            include "secrets.php";
            include "ccValidation.php";

            $userID = 1002;

            //connect to database
            $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
        ?>
        <script> var currentTab = 0; </script>
    </head>
    <body>
        <!-- Submission CC Validation (Full Page) -->
        <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                $valid = validateCheckout($database);

                if($valid)
                {
                    //navigate to confirmation page with transaction ID
                }
                else
                {
                    echo "<script> currentTab = 3; </script>";
                }
            }
        ?>

        <!-- Form Entry (Left Side) -->
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
                <p><input id="matchShipping" type="checkbox">Same as Shipping</p>

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
                <p><input id="cardName" name="cardName" placeholder="Card Name..." value="<?php echo $_POST['cardName']; ?>" oninput="this.className = ''"></p>
                <p><input id="cardMonth" name="cardMonth" placeholder="MM..." value="<?php echo $_POST['cardMonth']; ?>" oninput="this.className = ''"></p>
                <p><input id="cardYear" name="cardYear" placeholder="YYYY..." value="<?php echo $_POST['cardYear']; ?>" oninput="this.className = ''"></p>
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

        </form>

        <!-- Cart Summary (Right Side) -->

        <script src="js/checkout_script.js"></script>
    </body>
</html>