<?php
session_start();

//include "database_calls.php"

// --- DB Connection and Logic ---
// $dsn = "";
// $username = "";
// $password = "";
// $pdo = establishDB($dsn, $username, $password);

//missing logic to retreve userID from signon page and DB login

$userID = $_GET['userID'] ?? $_SESSION['userID'] ?? null;
$orderStatusMessage = "";
$infoUpdateMessage = "";
$paymentUpdateMessage = "";

if (!$userID) {
    die("User ID is required to access this page.");
}

// 1. Check Order Status
if (isset($_POST['check_invoice'])) {
    $invoice = trim($_POST['invoice_number']);
    $stmt = $pdo->prepare("SELECT status FROM OrderStatus WHERE invoiceNumber = ?");
    $stmt->execute([$invoice]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $orderStatusMessage = $result ? $result['status'] : "Invoice not found.";
}

// 2. Update Personal Info
if (isset($_POST['update_info'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $zipcode = $_POST['zipcode'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("UPDATE UserAccount SET firstName=?, lastName=?, shippingAddress=?, state=?, zipcode=?, phone=?, email=? WHERE userID=?");
    $stmt->execute([$firstName, $lastName, $address, $state, $zipcode, $phone, $email, $userID]);
    $infoUpdateMessage = "Personal information updated successfully.";
}

// 3. Update Payment Info
if (isset($_POST['update_payment'])) {
    $cardNumber = $_POST['card_number'];
    $expiration = $_POST['expiration'];
    $cvv = $_POST['cvv'];

    // Check if payment entry exists
    $check = $pdo->prepare("SELECT * FROM PaymentData WHERE userID = ?");
    $check->execute([$userID]);

    if ($check->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE PaymentData SET cardNumber=?, expiration=?, cvv=? WHERE userID=?");
    } else {
        $stmt = $pdo->prepare("INSERT INTO PaymentData (cardNumber, expiration, cvv, userID) VALUES (?, ?, ?, ?)");
    }

    $stmt->execute([$cardNumber, $expiration, $cvv, $userID]);
    $paymentUpdateMessage = "Payment information updated successfully.";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Returing User</title>
    <link rel="icon" href="wrench.png" type="image/x-icon">
    <style>
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

<nav>
	<a href=main_page.php>Home</a>
	<a href=esignon_page.php>Staff</a>
	<a href=cart.php>Cart</a>
</nav>
<div style="margin-top: 30px;"></div>

<div class="container">

    <!-- Left Side: Order Status -->
    <div class="left">
        <div class="section">
            <h2>Check Order Status</h2>
            <form method="post">
                <label>Invoice Number:</label><br>
                <input type="text" name="invoice_number" required><br>
                <input type="submit" name="check_invoice" value="Check Status">
            </form>
            <?php if ($orderStatusMessage): ?>
                <p><strong>Status:</strong> <?= htmlspecialchars($orderStatusMessage) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Side: Personal + Payment Info -->
    <div class="right">
        <div class="section">
            <h2>Update Personal Information</h2>
            <form method="post">
                <label>First Name:</label><br>
                <input type="text" name="first_name" required><br>

                <label>Last Name:</label><br>
                <input type="text" name="last_name" required><br>

                <label>Shipping Address:</label><br>
                <input type="text" name="address" required><br>

                <label>State:</label><br>
                <input type="text" name="state" maxlength="2" required><br>

                <label>Zipcode:</label><br>
                <input type="number" name="zipcode" required><br>

                <label>Phone:</label><br>
                <input type="number" name="phone" required><br>

                <label>Email:</label><br>
                <input type="email" name="email" required><br>

                <input type="submit" name="update_info" value="Update Info">
            </form>
            <?php if ($infoUpdateMessage): ?>
                <p><strong><?= htmlspecialchars($infoUpdateMessage) ?></strong></p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Update Payment Information</h2>
            <form method="post">
                <label>Card Number:</label><br>
                <input type="text" name="card_number" required><br>

                <label>Expiration (MMYY):</label><br>
                <input type="text" name="expiration" required><br>

                <label>CVV:</label><br>
                <input type="text" name="cvv" required><br>

                <input type="submit" name="update_payment" value="Update Payment">
            </form>
            <?php if ($paymentUpdateMessage): ?>
                <p><strong><?= htmlspecialchars($paymentUpdateMessage) ?></strong></p>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>
