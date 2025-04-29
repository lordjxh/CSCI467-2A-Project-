<?php
session_start();

include "secrets.php";
include "php_functions/database_functions.php";

$pdo = establishDB($databaseHost, $databaseUsername, $databasePassword);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staffID = $_POST['staffID'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($staffID) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT staffID, password, isAdmin FROM staff WHERE staffID = :staffID");
        $stmt->bindParam(':staffID', $staffID);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['staffID'] = $user['staffID'];   // Store staffID in session
            $_SESSION['isAdmin'] = $user['isAdmin'];   // Store isAdmin in session
            $_SESSION['logged_in'] = true;

            // Redirect based on admin status
            if ($user['isAdmin'] == 1) {
                header("Location: admin_page.php?staffID=" . urlencode($user['staffID']));
            } else {
                header("Location: wh_page.php?staffID=" . urlencode($user['staffID']));
            }
            exit();
        } else {
            echo "<p style='color:red;'>Invalid staff ID or password.</p>";
        }
    } else {
        echo "<p style='color:red;'>Both fields are required.</p>";
    }
}
?>
	
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Employee Login</title>
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
            max-width: 300px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        label, input {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            width: auto;
        }
    </style>
</head>
<body>
	
<h1>Warehouse Employee Login Portal</h1>
	
<nav>
	<a href=main_page.php>Home</a>
	<a href=signon_page.php>Sign-In</a>
	<a href=cart.php>Cart</a>
</nav>

<form method="post" action="">
	<label for="staffID">Staff ID:</label>
    	<input type="text" name="staffID" id="staffID" required>
	
    	<label for="password">Password:</label>
    	<input type="password" name="password" id="password" required>
	
    	<input type="submit" value="Login">
</form>

</body>
</html>
