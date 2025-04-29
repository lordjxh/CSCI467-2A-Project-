<!-- 
    Group 2A - CSCI 467 Spring 2025
    add_employee.php - This page is created for administrators to add new employees to the database. Takes all inputs required for 'Staff' table, validates
        all necessary values are included, and that the username is not already taken, then adds entry.

-->

<?php
    include "secrets.php";
    include "php_functions/database_functions.php";
    include "php_functions/admin_functions.php";

    session_start();

    //establish connection(s) to database(s)
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

    //if the user is not logged in as an admin, redirect to admin login
    if($_SESSION['isAdmin'] == false)
    {
        header("Location: esignon_page.php");
        exit();
    }

    //calls addNewEmployee() when called
    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        $result = addNewEmployee($database);

        if($result == 0)
        {
            $isAdded = true;
            $message = "Successfully added employee.";
        }
        else if($result == 1)
        {
            $isAdded = false;
            $message = "Failed to add employee: one or more required value was missing.";
        }
        else if($result == 2)
        {
            $isAdded = false;
            $message = "Failed to add employee: username was already taken.";
        }
    }
?>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <link rel="stylesheet" href="css/add_employee.css">
        <title>Add Employee</title>
    </head>
    <body>
        <header>
            <nav>
                <a href="admin_page.php" class="button"><- Go Back</a>
            </nav>
        </header>
        <div style="margin-top: 30px;"></div>
            <div class="left">
                <div class="section">
                    <?php if(isset($message)) {echo "<p>" . $message . "</p>";} ?>
                    <h2>Add New Employee</h2>
                    <form id="regForm" method="post">
                        <p><input id="firstName" name="firstName" placeholder="First name..." maxlength="32" value="<?php if($isAdded == false) {echo $_POST['firstName'];} ?>" oninput="this.className = ''"></p>
                        <p><input id="lastName" name="lastName" placeholder="Last name..." maxlength="32" value="<?php if($isAdded == false) {echo $_POST['lastName'];} ?>" oninput="this.className = ''"></p>
                            
                        <p><input id="address" name="address" placeholder="Address..." maxlength="64" value="<?php if($isAdded == false) {echo $_POST['address'];} ?>" oninput="this.className = ''"></p>
                        <p><input id="city" name="city" placeholder="City..." maxlength="48" value="<?php if($isAdded == false) {echo $_POST['city'];} ?>" oninput="this.className = ''"></p>
                        <p><input id="state" name="state" placeholder="State..." maxlength="2" value="<?php if($isAdded == false) {echo $_POST['state'];} ?>" oninput="this.className = ''"></p>
                        <p><input id="zipcode" name="zipcode" placeholder="Zipcode..." maxlength="10" value="<?php if($isAdded == false) {echo $_POST['zipcode'];} ?>" oninput="this.className = ''"></p>
                            
                        <p><input type="email" id="email" name="email" placeholder="Email..." maxlength="32" value="<?php if($isAdded == false) {echo $_POST['email'];} ?>" oninput="this.className = ''"></p>
                        <p><input type="tel" id="phone" name="phone" placeholder="Phone..." maxlength="14" value="<?php if($isAdded == false) {echo $_POST['phone'];} ?>" oninput="this.className = ''"></p>

                        <p><input id="username" name="username" placeholder="Username..." maxlength="32" value="<?php if($isAdded == false) {echo $_POST['username'];} ?>" oninput="this.className = ''"></p>
                        <p><input id="password" name="password" placeholder="Password..." maxlength="32" value="<?php if($isAdded == false) {echo $_POST['password'];} ?>" oninput="this.className = ''"></p>

                        <input type="submit" name="add" value="add">
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>