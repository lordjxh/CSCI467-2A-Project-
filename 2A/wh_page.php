<?php
session_start();


include "secrets.php";
include "php_functions/database_functions.php";


// Check login status
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: esignon_page.php');
    exit();
}

// Connect to database
$pdo = establishDB($databaseHost, $databaseUsername, $databasePassword);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoiceNO'])) {
    $selectedInvoice = $_POST['invoiceNO'];

    $stmt = $pdo->prepare("UPDATE InvoiceDB SET shippingFlag = 'S' WHERE invoiceNO = :invoiceNO");
    $stmt->execute(['invoiceNO' => $selectedInvoice]);
}

// Get all open invoices
$sql = "SELECT invoiceNO, userID, subtotal, shippingCost, grandTotal, shippingFlag 
        FROM InvoiceDB 
        WHERE fulfillmentStatus IS NULL;";
$rs = getSQL($pdo, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Portal</title>
    <link rel="icon" href="img/wrench.png" type="image/x-icon">
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
    
    <h1> Warehouse Fulfillment </h1>        

    <nav>
        <a href="main_page.php">Home</a>
        <a href="inventory_management.php">Update Inventory</a>
        <a href="signout_page.php">Logout</a>
    </nav>

    <h2>Open Orders</h2>
    <p>Welcome Back! Select an invoice to create a shipping label.</p>

<form action="" method="post">
    <table>
        <thead>
            <tr>
                <th>Select</th>
                <th>Invoice #</th>
                <th>User ID</th>
                <th>Subtotal</th>
                <th>Shipping Cost</th>
                <th>Grand Total</th>
                <th>Shipping Flag</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($invoice = $rs->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><input type="radio" name="invoiceNO" value="<?= htmlspecialchars($invoice['invoiceNO']) ?>" required></td>
                    <td><?= htmlspecialchars($invoice['invoiceNO']) ?></td>
                    <td><?= htmlspecialchars($invoice['userID']) ?></td>
                    <td>$<?= number_format($invoice['subtotal'], 2) ?></td>
                    <td>$<?= number_format($invoice['shippingCost'], 2) ?></td>
                    <td>$<?= number_format($invoice['grandTotal'], 2) ?></td>
                    <td><?= htmlspecialchars($invoice['shippingFlag'] ?? '-') ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <button type="submit">Create Label for Selected Invoice</button>
</form>

</body>
</html>
