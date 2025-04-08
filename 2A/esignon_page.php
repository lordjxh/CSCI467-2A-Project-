<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = $_POST['userID'] ?? '';
    $password = $_POST['password'] ?? '';

}
?>


<?php
//This file is a series of PHP functions that can work with the PDO object and databases.

//establishDB() - creates a $PDO object conection to a database server
function establishDB($dsn, $username, $password)
{
    //establish connection to MariaDB and set PDO object
    try{ //if something goes wrong, an exception is thrown below
        $pdo = new PDO($dsn, $username, $password);
    }
    catch(PDOexception $error){ //handles exception(s)
        echo "Connection to DB failed" . $error->getMessage();
    }
    
    return $pdo;
}

//getSQL() - takes a SQL statement and returns rows retrieved
function getSQL($pdo, $statement)
{
    $rs = $pdo->query($statement);
    return $rs;
}

//debugPrintRows() - for debugging, will print all rows from a SQL query array
function debugPrintRows($rs)
{
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        echo "<h3>";
        foreach($row as $key=>$value)
        {
            echo $value;
            echo " ";
        }

        echo "</h3>";
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

<nav>
	<a href=>Home</a>
	<a href=>Sign-In</a>
	<a href=>Cart</a>
</nav>



<h2>Warehouse Employee Login</h2>
<form method="post" action="">
    <label for="userID">User ID:</label>
    <input type="text" name="userID" id="userID" required>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>

    <input type="submit" value="Login">
</form>

</body>
</html>
