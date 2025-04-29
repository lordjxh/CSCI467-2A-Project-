<?php
session_start();


include "secrets.php";
include "php_functions/user_functions.php";
include "php_functions/database_functions.php";


// Check login status
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: esignon_page.php');
    exit();
}

// Connect to database
$pdo = establishDB($databaseHost, $databaseUsername, $databasePassword);


// Get all open invoices
$sql = "SELECT invoiceNO, userID, subtotal, shippingCost, grandTotal FROM InvoiceDB WHERE fulfillmentStatus = 'N';";
$rs = getSQL($pdo, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Portal</title>
    <link rel="icon" href="wrench.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
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
        form {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        label, input {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            width: auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .logout-link {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            color: #555;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
    <h1> Warehouse Fulillment </h1>        

<!-- Navigation Bar -->
    <nav>
        <a href="main_page.php">Home</a>
        <a href="inventory_management.php">Update Inventory</a>
        <a href="signout_page.php">Logout</a>
    </nav>

    <h2>Open Orders</h2>
    <p>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>! Select an invoice to create a shipping label.</p>

    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>User ID</th>
                <th>Subtotal</th>
                <th>Shipping Cost</th>
                <th>Grand Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display each unfulfilled invoice
            while ($invoice = $rs->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($invoice['invoiceNO']) . "</td>";
                echo "<td>" . htmlspecialchars($invoice['userID']) . "</td>";
                echo "<td>$" . number_format($invoice['subtotal'], 2) . "</td>";
                echo "<td>$" . number_format($invoice['shippingCost'], 2) . "</td>";
                echo "<td>$" . number_format($invoice['grandTotal'], 2) . "</td>";
                echo "<td>";
                echo "<form action='create_shipping_label.php' method='post' style='margin:0;'>";
                echo "<input type='hidden' name='invoiceNO' value='" . htmlspecialchars($invoice['invoiceNO']) . "'>";
                echo "<button type='submit'>Create Label</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>

</body>
</html>
