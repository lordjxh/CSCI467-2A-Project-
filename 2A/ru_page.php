<?php
// Start a new or resume an existing session
session_start();

include "secrets.php";
include "php_functions/database_functions.php";

// Check login status
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: signon_page.php');
    exit();
}

// Connect to database
$pdo = establishDB($databaseHost, $databaseUsername, $databasePassword);

// Retrieve userID either from GET parameters, session, or set as null if missing
$userID = $_GET['userID'] ?? $_SESSION['userID'] ?? null;

// For status messages
$orderStatusMessage = "";
$infoUpdateMessage = "";
$paymentUpdateMessage = "";

// If userID is not available, stop further execution
if (!$userID) {
    die("User ID is required to access this page.");
}

// Logic to check Order Status 
if (isset($_POST['check_invoice'])) {
    // Sanitize input
    $invoice = trim($_POST['invoice_number']);
    
    // Query InvoiceDB for the fulfillment status
    $stmt = $pdo->prepare("SELECT fulfillmentStatus FROM InvoiceDB WHERE invoiceNO = ?");
    $stmt->execute([$invoice]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Interpret and display status or error message
    if ($result) {
        $statusMap = ['P' => 'Processing', 'S' => 'Shipped', 'C' => 'Completed', 'X' => 'Cancelled'];
        $statusCode = strtoupper($result['fulfillmentStatus']);
        $orderStatusMessage = $statusMap[$statusCode] ?? "Unknown status code: $statusCode";
    } else {
        $orderStatusMessage = "Invoice not found.";
    }
}

// Logic to update Personal Info
if (isset($_POST['update_info'])) {
    // Collect personal info from POST data
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $zipcode = $_POST['zipcode'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // Update the UserAccount table with new information
    $stmt = $pdo->prepare("UPDATE UserAccount SET firstName=?, lastName=?, shippingAddress=?, state=?, zipcode=?, phone=?, email=? WHERE userID=?");
    $stmt->execute([$firstName, $lastName, $address, $state, $zipcode, $phone, $email, $userID]);
    
    // Set success message
    $infoUpdateMessage = "Personal information updated successfully.";
}

// Logic to Update Payment Info 
if (isset($_POST['update_payment'])) {
    // Collect payment info from POST data
    $cardNumber = (int)$_POST['card_number'];
    $expiration = (int)$_POST['expiration']; // Format: YYYYMM
    $cvv = (int)$_POST['cvv'];

    // Check if a payment record already exists for this user
    $check = $pdo->prepare("SELECT * FROM PaymentData WHERE userID = ?");
    $check->execute([$userID]);

    // Prepare either an UPDATE or INSERT statement
    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE PaymentData SET cardNumber=?, expiration=?, cvv=? WHERE userID=?");
    } else {
        $stmt = $pdo->prepare("INSERT INTO PaymentData (cardNumber, expiration, cvv, userID) VALUES (?, ?, ?, ?)");
    }

    // Execute the appropriate statement
    $stmt->execute([$cardNumber, $expiration, $cvv, $userID]);
    
    // Set success message
    $paymentUpdateMessage = "Payment information updated successfully.";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Returning User</title>
    <link rel="icon" href="wrench.png" type="image/x-icon">
    <style>
        /* Basic page styling */ 
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        nav {
            background-color: #808080;
            overflow: hidden;
            opacity: .6;
        }
        nav a {
            float: left;
            display: block;
            color: #000000;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            display: flex;
            flex-direction: row;
            gap: 40px;
        }
        .left, .right {
            flex: 1;
        }
        .section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        input[type=text], input[type=email], input[type=number] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        input[type=submit] {
            padding: 10px 20px;
        }
        h1 {
            margin-bottom: 40px;
        }
    </style>
</head>

<body>

<h1>Returning User Dashboard</h1>

<!-- Logic for nav Bar --> 
<nav>
	<a href="main_page.php">Home</a>
	<a href="esignon_page.php">Staff</a>
	<a href="cart.php">Cart</a>
</nav>

<div style="margin-top: 30px;"></div>

<div class="container">

    <div class="left">
        <div class="section">
            <h2>Check Order Status</h2>
            <form method="post">
                <label>Invoice Number:</label><br>
                <input type="number" name="invoice_number" required><br>
                <input type="submit" name="check_invoice" value="Check Status">
            </form>
		
            <?php if (!empty($orderStatusMessage)): ?>
                <p><strong>Status:</strong> <?= htmlspecialchars($orderStatusMessage) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="right">

        <!-- Update Personal Information -->
        <div class="section">
            <h2>Update Personal Information</h2>
            <form method="post">
                <label>First Name:</label><br>
                <input type="text" name="first_name" required><br>

                <label>Last Name:</label><br>
                <input type="text" name="last_name" required><br>

                <label>Shipping Address:</label><br>
                <input type="text" name="address" required><br>

                <label>State (2-letter):</label><br>
                <input type="text" name="state" maxlength="2" pattern="[A-Za-z]{2}" title="Two-letter state abbreviation" required><br>

                <label>Zipcode:</label><br>
                <input type="text" name="zipcode" pattern="\d{5}" maxlength="5" required><br>

                <label>Phone:</label><br>
                <input type="tel" name="phone" pattern="\d{10}" maxlength="10" title="10-digit phone number" required><br>

                <label>Email:</label><br>
                <input type="email" name="email" required><br>

                <input type="submit" name="update_info" value="Update Info">
            </form>

            <?php if (!empty($infoUpdateMessage)): ?>
                <p><strong><?= htmlspecialchars($infoUpdateMessage) ?></strong></p>
            <?php endif; ?>
        </div>

        <!-- Update Payment Information -->
        <div class="section">
            <h2>Update Payment Information</h2>
            <form method="post">
                <label>Card Number:</label><br>
                <input type="text" name="card_number" pattern="\d{13,16}" maxlength="16" title="13 to 16-digit card number" required><br>

                <label>Expiration (MMYY):</label><br>
                <input type="text" name="expiration" pattern="\d{4}" maxlength="4" title="Enter as MMYY" required><br>

                <label>CVV:</label><br>
                <input type="text" name="cvv" pattern="\d{3,4}" maxlength="4" title="3 or 4-digit CVV" required><br>

                <input type="submit" name="update_payment" value="Update Payment">
            </form>

            <?php if (!empty($paymentUpdateMessage)): ?>
                <p><strong><?= htmlspecialchars($paymentUpdateMessage) ?></strong></p>
            <?php endif; ?>
        </div>

    </div>

</div>


</body>
</html>
