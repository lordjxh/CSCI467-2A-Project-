<?php
//Group 2A - CSCI467 Spring 2025
//admin_functions - a PHP file for all functions used for administrator functionality. Primarily used with shipping_weights.php, and requires
//database_functions.php as a dependency.

//getShippingWeights() - calls to database and returns all the shipping weight values in the 'ShippingWeights' table
//inputs -
    //$database - the database $pdo initialized
//output - $weights - an array of all the weight values
function getShippingWeights($database)
{
    $statement = "SELECT * FROM ShippingWeights;";
    $weights = getSQL($database, $statement);
    return $weights;
}

//setShippingWeights() - when the shipping_weights.php form is submitted, this function updates all values in the 'ShippingWeights' table
//inputs -
    //$database - the database $pdo initialized
//output - updates 'ShippingWeights' table with updatedValues
function setShippingWeights($database)
{
    $index = 0; //holds current position
    $total = $_POST['count']; //holds the total count of weight values

    while ($index < $total) //while index is less than the total, process the current index
    {
        $id = $_POST[$index];
        $minWeight = $_POST['weightMin_' . $index];
        $maxWeight = $_POST['weightMax_' . $index];
        $percent = $_POST['percent_' . $index];

        $statement = "UPDATE ShippingWeights SET minimumWeight = " . $minWeight . ", maximumWeight = " . $maxWeight . ", shippingPercent = " .
            $percent . " WHERE weightID = " . $id . ";";

        updateDatabaseValue($database, $statement);
        $index++;
    }

    echo "<p>Weights updated successfully</p>";
}

//printShippingWeights() - prints all weight values into a table with form entries for modifying values
//inputs -
    //$weights - an array of weight values created from getShippingWeights()
//output - prints HTML elements to page with form for submission
function printShippingWeights($weights)
{
    echo "<form id=\"regForm\" method=\"post\">";
    echo "<table>";

    echo "<tr><td>Minimum $</td><td>Maximum $</td><td>Percentage</td></tr>";

    $index = 0; //holds current position

    foreach($weights as $w) //for each value in the weights array, print into the table with inputs for changing amount(s)
    {
        $id[$index] = $w['weightID'];
        $minWeight[$index] = $w['minimumWeight'];
        $maxWeight[$index] = $w['maximumWeight'];
        $percent[$index] = $w['shippingPercent'];

        echo "<tr class=\"weightRow\">";
        echo "<input id=\"" . $index . "\" name=\"" . $index . "\" type=\"hidden\" value=\"" . $id[$index] . "\">";
        echo "<td><input id=\"weightMin_" . $index . "\" name=\"weightMin_" . $index . "\" value=\"" . $minWeight[$index] . "\"></p>";
        echo "<td><input id=\"weightMax_" . $index . "\" name=\"weightMax_" . $index . "\" value=\"" . $maxWeight[$index] . "\"></p>";
        echo "<td><input id=\"percent_" . $index . "\" name=\"percent_" . $index . "\" value=\"" . $percent[$index] . "\"></p>";
        echo "</tr>";

        $index++; //increments index by one
    }

    echo "</table>";
    echo "<input id=\"count\" name=\"count\" type=\"hidden\" value=\"" . $index . "\">";
    echo "<button type=\"submit\" id=\"update\" name=\"update\" class=\"button\">Update</button>";
    echo "</form>";
}

//addNewEmployee() - called from add_employee.php, will take $_POST values after submission and validates input. If successful, adds a new
//employee into the 'Staff' table.
//inputs -
    //$database - the database $pdo initialized
//output - inserts new value into 'Staff' table if successful, returns an int value based on result (0, 1, or 2)
function addNewEmployee($database)
{
    //
    //Step 1 - retrieve values and store into local array

    $employee[0] = $_POST['firstName'];
    $employee[1] = $_POST['lastName'];
    $employee[2] = $_POST['address'];
    $employee[3] = $_POST['city'];
    $employee[4] = $_POST['state'];
    $employee[5] = $_POST['zipcode'];
    $employee[6] = $_POST['email'];
    $employee[7] = $_POST['phone'];
    $employee[8] = $_POST['username'];
    $employee[9] = $_POST['password'];

    //
    //Step 2 - validate entries
    for($c = 0; $c < 10; $c++)
    {
        if($employee[$c] == "") //if value is blank, return error 1 for missing
        {
            return 1;
        }
    }

    $statement = "SELECT staffID FROM Staff WHERE staffUserName = '" . $employee[8] . "';";
    $rs = getSQL($database, $statement);
    $userTaken = extractSingleValue($rs);

    if($userTaken != NULL) //if at least one value is found, return error 2 for taken username
    {
        return 2;
    }

    //
    //Step 3 - Add new employee to table

    $statement2 = "INSERT INTO Staff(staffUserName, staffFirstname, staffLastName, staffAddress, staffCity, staffState, staffZipcode, staffPhone, staffEmail, staffPassword) VALUES ('" .
        $employee[8] . "', '" . $employee[0] . "', '" . $employee[1] . "', '" . $employee[2] . "', '" . $employee[3] . "', '" . $employee[4] . "', '" . $employee[5] . "', '" . $employee[7] . "', '" . $employee[6] . "', '" . $employee[9] . "');";

    insertDatabaseValue($database, $statement2);

    return 0;
}

//getEmployeeDetail() - based on the specified employee username, calls the database to retrieve the employee's record and store into a variable for use with later functions
//inputs -
    //$database - the database $pdo initialized
    //$employeeUserName - a username that is specified by the user during search
//output - $employee - an array of all elements of a single employee from the 'Staff' table, or null if no employee could be found
function getEmployeeDetail($database, $employeeUserName)
{
    //statement to retrieve employee's details
    $statement = "SELECT * FROM Staff WHERE staffUserName = '" . $employeeUserName . "';";
    $rs = getSQL($database, $statement);

    foreach($rs as $employeeOut) //for each, forces only a single employee to show (no duplicate usernames). If none, returns null.
    {
        //assigns each extracted value into an array
        $employee['staffID'] = $employeeOut['staffID'];
        $employee['firstName'] = $employeeOut['staffFirstName'];
        $employee['lastName'] = $employeeOut['staffLastName'];
        $employee['address'] = $employeeOut['staffAddress'];
        $employee['city'] = $employeeOut['staffCity'];
        $employee['state'] = $employeeOut['staffState'];
        $employee['zipcode'] = $employeeOut['staffZipcode'];
        $employee['email'] = $employeeOut['staffEmail'];
        $employee['phone'] = $employeeOut['staffPhone'];
        $employee['username'] = $employeeOut['staffUserName'];
        $employee['password'] = $employeeOut['staffPassword'];
        $employee['currentEmployee'] = $employeeOut['currentEmployee'];
        $employee['isAdmin'] = $employeeOut['isAdmin'];

        return $employee; //returns array
    }

    return NULL;
}

//setEmployeeStatus() - called from set_employee_status.php, and will take $_POST values after submission and validates input. If successful, updates
//employee information back into the 'Staff' table.
//inputs -
    //$database - the database $pdo initialized
//output - updates employee values into 'Staff' table if successful, returns an int value based on result (0, 1, 2, or 3)
function setEmployeeStatus($database)
{
    //
    //Step 1 - retrieve values and store into local array

    $employee[0] = $_POST['staffID'];
    $employee[1] = $_POST['firstName'];
    $employee[2] = $_POST['lastName'];
    $employee[3] = $_POST['address'];
    $employee[4] = $_POST['city'];
    $employee[5] = $_POST['state'];
    $employee[6] = $_POST['zipcode'];
    $employee[7] = $_POST['email'];
    $employee[8] = $_POST['phone'];
    $employee[9] = $_POST['username'];
    $employee[10] = $_POST['password'];
    $employee[11] = $_POST['currentEmployee'] ? '1' : '0';
    $employee[12] = $_POST['isAdmin'] ? '1' : '0';

    //
    //Step 2 - validate entries
    for($c = 1; $c < 10; $c++)
    {
        if($employee[$c] == "") //if value is blank, return error 1 for missing
        {
            return 1;
        }
    }

    //check that the userID being changed is not already taken
    $statement = "SELECT staffID, staffUserName FROM Staff WHERE staffUserName = '" . $employee[9] . "';";
    $rs = getSQL($database, $statement);

    foreach($rs as $value)
    {
        if($employee[0] != $value['staffID']) //if at least one value is found, return error 2 for taken username
        {
            return 2;
        }
    }

    //if revoking admin status, make sure there is always at least one admin in employees
    if($_POST['originalAdminStatus'] == 1)
    {
        if($employee[12] == 0)
        {
            //get count of all employees with admin status
            $statement2 = "SELECT Count(staffID) FROM Staff WHERE isAdmin = '1';";
            $rs2 = getSQL($database, $statement2);
            $adminCount = extractSingleValue($rs2);

            if(($adminCount - 1) == 0) //if the admin count after removal
            {
                return 3;
            }
        }
    }

    //
    //Step 3 - Add new employee to table

    $statement3 = "UPDATE Staff SET staffFirstName = '" . $employee[1] . "', staffLastName = '" . $employee[2] . "', staffAddress = '" . $employee[3] . "', staffCity = '" .
        $employee[4] . "', staffState = '" . $employee[5] . "', staffZipcode = '" . $employee[6] . "', staffPhone = '" . $employee[8] . "', staffEmail = '" . $employee[7] .
        "', staffUserName = '" . $employee[9] . "', staffPassword = '" . $employee[10] . "', currentEmployee = '" . $employee[11] . "', isAdmin = '" . $employee[12] .
        "' WHERE staffID = '" . $employee[0] . "';";

    updateDatabaseValue($database, $statement3);

    return 0;
}

//printEmployeeDetail() - prints all customer values into a table with form entries for modifying values
//inputs -
    //$employee - an array of a single customer's values created from getEmployeeDetail()
//output - prints HTML elements to page with form for submission
function printEmployeeDetail($employee)
{
    echo "<table>";
    echo "<tr><td>First Name</td><td>Last Name</td><td>Address</td><td>City</td><td>State</td><td>Zipcode</td><td>Email</td><td>Phone</td><td>Username</td><td>Password</td><td>Current Employee</td><td>Admin</td></tr>";

    echo "<tr>";
    echo "<input type=\"hidden\" id=\"staffID\" name=\"staffID\" value=\"" . $employee['staffID'] . "\">";
    echo "<input type=\"hidden\" id=\"originalAdminStatus\" name=\"originalAdminStatus\" value=\"" . ($employee['isAdmin'] ? '1' : '0') . "\">";

    echo "<td><input id=\"firstName\" name=\"firstName\" placeholder=\"First name...\" maxlength=\"32\" value=\"" . $employee['firstName'] . "\" oninput=\"this.className = ''\"></td>";
    echo "<td><input id=\"lastName\" name=\"lastName\" placeholder=\"Last name...\" maxlength=\"32\" value=\"" . $employee['lastName'] . "\" oninput=\"this.className = ''\"></td>";
            
    echo "<td><input id=\"address\" name=\"address\" placeholder=\"Address...\" maxlength=\"64\" value=\"" . $employee['address'] . "\" oninput=\"this.className = ''\"></td>";
    echo "<td><input id=\"city\" name=\"city\" placeholder=\"City...\" maxlength=\"48\" value=\"" . $employee['city'] . "\" oninput=\"this.className = ''\"></td>";
    echo "<td><input id=\"state\" name=\"state\" placeholder=\"State...\" maxlength=\"2\" value=\"" . $employee['state'] . "\" oninput=\"this.className = ''\"></td>";
    echo "<td><input id=\"zipcode\" name=\"zipcode\" placeholder=\"Zipcode...\" maxlength=\"10\" value=\"" . $employee['zipcode'] . "\" oninput=\"this.className = ''\"></td>";
            
    echo "<td><input type=\"email\" id=\"email\" name=\"email\" placeholder=\"Email...\" maxlength=\"32\" value=\"" . $employee['email'] . "\" oninput=\"this.className = ''\"></td>";
    echo "<td><input type=\"tel\" id=\"phone\" name=\"phone\" placeholder=\"Phone...\" maxlength=\"14\" value=\"" . $employee['phone'] . "\" oninput=\"this.className = ''\"></td>";

    echo "<td><input id=\"username\" name=\"username\" placeholder=\"Username...\" maxlength=\"32\" value=\"" . $employee['username'] . "\" oninput=\"this.className = ''\"></td>";
    echo "<td><input id=\"password\" name=\"password\" placeholder=\"Password...\" maxlength=\"32\" value=\"" . $employee['password'] . "\" oninput=\"this.className = ''\"></td>";

    echo "<td><input type=\"checkbox\" id=\"currentEmployee\" name=\"currentEmployee\" value=\"1\" " . ($employee['currentEmployee'] ? 'checked' : '') . "></td>";
    echo "<td><input type=\"checkbox\" id=\"isAdmin\" name=\"isAdmin\" value=\"1\" " . ($employee['isAdmin'] ? 'checked' : '') . "></td>";
    echo "</tr>";

    echo "</table>";
}
?>