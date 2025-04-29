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

//addNewEmployee() - called from add_employee.php, will take $_POST values after submission and validate input. If successful, adds a new
//employee into the 'Staff' table.
//inputs -
    //$database - the database $pdo initialized
//output - inserts new value into 'Staff' table if successful, returns an int value based on result (0, 1, or 2)
function addNewEmployee($database)
{
    //
    //Step 1- retrieve values and store into local array

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
?>