<?php
session_start();
//require_once("database_functions.php");

// Replace prior to runtime
//$dsn = "";
//$dbUser = "";
//$dbPass = "";

//$pdo = establishDB($dsn, $dbUser, $dbPass);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_POST['userID'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($userID) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT userid, password FROM staff WHERE userid = :userid");
        $stmt->bindParam(':userid', $userID);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        //upon successful signin redirect to warehouse landing page and pass along userID

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['userid'] = $user['userid'];
            header("Location: wh_page.php?userid=" . urlencode($user['userid']));
            exit();
        } else {    
            echo "<p style='color:red;'>Invalid user ID or password.</p>";
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
    <label for="userID">User ID:</label>
    <input type="text" name="userID" id="userID" required>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>

    <input type="submit" value="Login">
</form>

</body>
</html>
