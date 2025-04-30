<!-- 
    Group 2A - CSCI 467 Spring 2025
    admin_page.php - This page is created for administrators. It serves as a navigation for administrator functionality. This includes
        advanced searchup of invoices, the ability to modify shipping weights, loading new legacy products into the database, adding
        new employees into the database, and the ability to invoke/revoke admin status.

-->

<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/admin_functions.php";

    session_start();
    
    //establish connection(s) to database(s)
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    //if the user is not logged in as an admin, redirect to admin login
    if($_SESSION['isAdmin'] == false)
    {
        if($_SESSION['logged_in'] == true) //if logged in, throw HTML error for unauthorized access
        {
            http_response_code(402);
            die("ERROR: You are unauthorized to access this page. If this is in error, please inform a system admin.");
        }
        else //else proceed with redirect
        {
            header("Location: esignon_page.php");
            exit();
        }
    }

    //calls loadLegacyProducts() when called
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $result = loadLegacyProducts($database, $legacyDB);
    }
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/admin_page.css">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <title> Admin </title>
    </head>
    <body>
        <h1>2A-CORP</h1>
        <nav>
            <a href="signout_page.php">Logout</a>
        </nav>
        <div style="margin-top: 30px;"></div>
            <div class="left">
                <div class="section">
                    <h2>Administrator Page</h2><br/>
                    <a class="button" href="invoice_search.php">Invoice Lookup</a><br/>
                    <a class="button" href="shipping_weights.php">Modify Shipping Weights</a><br/>
                    <a class="button" href="add_employee.php">Add New Employee</a><br/>
                    <a class="button" href="set_employee_status.php">Set Employee Status</a><br/>
                    <form id="regForm" method="post">
                        <a class="button" id="callLoadLegacy" name="callLoadLegacy" onclick="document.getElementById('regForm').submit()">Add New Legacy Products</input></a>
                    </form>
                    <?php echo "<p>" . $result . "</p>"; ?>
                </div>
            </div>
        </div>
    </body>
</html>