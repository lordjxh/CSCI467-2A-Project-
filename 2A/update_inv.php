<?php
session_start();

/*
// Check login status
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: esignon_page.php');
    exit();
}

// Load database functions
require_once 'database_functions.php';

// Database connection info
$dsn = ""; // Change 'your_database'
$username = ""; // Change this
$password = ""; // Change this

// Connect to database
$pdo = establishDB($dsn, $username, $password);


// Get all open invoices

// Handle quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantities'])) {
    foreach ($_POST['products'] as $productID => $quantities) {
        $storeQty = intval($quantities['storeQuantity']);
        $warehouseQty = intval($quantities['warehouseQuantity']);

        $stmt = $pdo->prepare("UPDATE Products SET storeQuantity = ?, warehouseQuantity = ? WHERE productID = ?");
        $stmt->execute([$storeQty, $warehouseQty, $productID]);
    }
    echo "<p style='color: green;'>Quantities updated successfully.</p>";
}

// Fetch products
$productStmt = $pdo->query("SELECT productID, storeQuantity, warehouseQuantity, legacyID FROM Products");
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receiving Desk Portal</title>
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

    <h1>Receiving Desk </h1>

    <!-- Navigation Bar -->
    <nav>
        <a href="main_page.php">Home</a>
        <a href="signout_page.php">Logout</a>
    </nav>

        <h2>Product Manager</h2>
    <form method="post">
        <table>
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Legacy ID</th>
                    <th>Store Quantity</th>
                    <th>Warehouse Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['productID']) ?></td>
                    <td><?= htmlspecialchars($product['legacyID']) ?></td>
                    <td>
                        <input type="number" name="products[<?= $product['productID'] ?>][storeQuantity]" value="<?= htmlspecialchars($product['storeQuantity']) ?>">
                    </td>
                    <td>
                        <input type="number" name="products[<?= $product['productID'] ?>][warehouseQuantity]" value="<?= htmlspecialchars($product['warehouseQuantity']) ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="update_quantities" class="submit-btn">Update Quantities</button>
    </form>

</body>
</html>
