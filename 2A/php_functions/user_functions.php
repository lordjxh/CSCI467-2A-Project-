<?php

//
// This file is a series of functions used regarding user details such as sign-on or assign an ID.
// It is dependent on database_functions.php and $_SESSION to operate.

//assignUserID() - for guest users, will create a userID from the Users table, separate from UserAcc.
//Inputs -
    //$database - the connected database to query
//Output - the ID of the last created User value
function assignUserID($database)
{
    $statement = "INSERT INTO Users(creationDate) VALUES ('" . date("Y-m-d H:m:s") . "');";
    insertDatabaseValue($database, $statement);

    $userID = $database->lastInsertID();

    return $userID;
}

//setLogOnAttributeValue - should be used with the following HTML element on most pages:
//  "<p><a href="" id="UserID" name="UserID" placeholder="Log In">*FUNCTION_CALL_HERE</a></p>"
//and will add appropriate text to the element. Be sure to update CSS as well.
//Inputs -
    //$database - the connected database to query, passed to another function
//Output - Updates element value.
function setLogOnAttributeValue($database)
{
    if($_SESSION['logged_in'] == true)
    {
        $statement = "SELECT firstName FROM UserAccount WHERE userID = " . $_SESSION['userID'] . ";";
        $rs = getSQL($database, $statement);
        $name = extractSingleValue($rs);

        echo "Welcome, " . $name . "!";
    }
    else
    {
        echo "Log On";
    }
}
?>