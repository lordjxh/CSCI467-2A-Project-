<?php
session_start();

include "secrets.php";
include "php_functions/database_functions.php";

$pdo = establishDB($databaseHost, $databaseUsername, $databasePassword);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $staffID = isset($_POST['staffID']) ? trim($_POST['staffID']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    $sql = $pdo->prepare("SELECT * FROM Staff WHERE staffID = :staffID AND staffPassword = :password");
    if (!$sql) {
        die("Error preparing SQL query: " . implode(", ", $pdo->errorInfo()));
    }

    $sql->bindParam(':staffID', $staffID, PDO::PARAM_INT);
    $sql->bindParam(':password', $password, PDO::PARAM_STR);
    $sql->execute();

    $result = $sql->fetch(PDO::FETCH_ASSOC);

    if ($result) {
	    
        session_regenerate_id(true);

        $_SESSION['logged_in'] = true;
        $_SESSION['staffID'] = $result['staffID'];
        $_SESSION['isAdmin'] = $result['isAdmin'];

        if ($result['isAdmin']) {
            header("Location: admin_page.php?staffID=" . urlencode($result['staffID']));
        } else {
            header("Location: wh_page.php?staffID=" . urlencode($result['staffID']));
        }
        exit();
    }

    echo "<p style='color:red;'>Invalid staff ID or password.</p>";
}
?>
	
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Employee Login</title>
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

<h2>Login</h2>
	
<form method="post" action="">
	<label for="staffID">Staff ID:</label>
    	<input type="text" name="staffID" id="staffID" required>
	
    	<label for="password">Password:</label>
    	<input type="password" name="password" id="password" required>
	
    	<input type="submit" value="Login">
</form>

</body>
</html>
