<!-- 
    Group 2A - CSCI 467 Spring 2025
    set_employee_status.php - This page is created for administrators to update employee accounts from the 'Staff' table. This includes standard form entry values such as name,
        address, phone, and email, but also changing username, password, and permissions across site. If an employee is not a current employee, they will be unable to log into the
        warehouse pages. Likewise if the employee is not an admin, they will be unable to access site pages like this one. Safeguards are also in place to keep at least one
        employee an admin, and to ensure usernames are not duplicated or values are missing.

-->

<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
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

    //handles form submission requests
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['update'])) //if update was pressed, call setEmployeeStatus() and set the result message of operation
        {
            $result = setEmployeeStatus($database);

            if($result == 0) //if $result is zero, the update was successful
            {
                $message = "Updated employee successfully.";
            }
            else if($result == 1) //else if $result is one, values that were required were missing
            {
                $message = "Failed to update employee: one or more required value was missing.";
            }
            else if($result == 2) //else if $result is two, the new username already exists in the table
            {
                $message = "Failed to update employee: the new username belongs to another employee.";
            }
            else if ($result == 3) //else if $result is three, there are not enough admins left after removal
            {
                $message = "Failed to update employee: there must be at least one employee with Admin status.";
            }
            else //all else, an unexpected error occurred when updating the employee
            {
                $message = "An unknown issue occurred when attempting to update employee. Please try again.";
            }
        }
        else if(isset($_POST['search'])) //if search was pressed, call getEmployeeDetail() and set message if search fails
        {
            $employee = getEmployeeDetail($database, $_POST['searchTerm']);

            if($employee == NULL)
            {
                $message = "Error: No employee was found with the specified username.";
            }
        }
    }
?>

<!-- Start of HTML Block -->

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="img/wrench.png">
        <link rel="stylesheet" href="css/set_employee_status.css">
        <title>Set Employee Status</title>
    </head>
    <body>
        <header>
            <nav>
                <a href="admin_page.php"><- Go Back</a>
            </nav>
        </header>
        <div style="margin-top: 30px;"></div>
            <div class="left">
                <div class="section">
                    <h2>Set Employee Status</h2>
                    <p><?php echo $message; ?></p>
                    <form id="regForm" method="post">
                        <input id="searchTerm" name="searchTerm" placeholder="Employee Username..." value=<?php echo $_POST['searchTerm']; ?>>
                        <button type="submit" id="search" name="search" class="button">Search</button>
                        <?php
                            if($_SERVER['REQUEST_METHOD'] == 'POST')
                            {
                                if($employee != NULL) //if the $employee array failed to get, surpress printing values
                                {
                                    echo "<button type=\"submit\" id=\"update\" name=\"update\" class=\"button\">Update</button>";
                                    printEmployeeDetail($employee);
                                }
                            }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>