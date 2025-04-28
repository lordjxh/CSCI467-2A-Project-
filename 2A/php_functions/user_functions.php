<?php
//Group 2A - CSCI467 Spring 2025
//user_functions - a PHP file for functions used regarding user details such as sign-on or assigning an ID.
//It is dependent on database_functions.php and $_SESSION to operate.


//assignUserID() - for guest users, will create a userID from the Users table, separate from UserAcc.
//Inputs -
    //$database - the PDO object with a valid database connected
//Output - the ID of the last created User value
function assignUserID($database)
{
    $statement = "INSERT INTO Users(creationDate) VALUES ('" . date("Y-m-d H:m:s") . "');";
    insertDatabaseValue($database, $statement);

    $userID = $database->lastInsertID();

    return $userID;
}

//setLogOnAttributeValue() - should be used with the following HTML element on most pages:
//  "<p><a href="" id="UserID" name="UserID" placeholder="Log In">*FUNCTION_CALL_HERE</a></p>"
//and will add appropriate text to the element. Be sure to update CSS as well.
//Inputs -
    //$database - the PDO object with a valid database connected, passed to another function
//Output - Updates element value.
function setLogOnAttributeValue($database)
{
    echo "<p class=\"user-message\">";

    if($_SESSION['logged_in'] == true)
    {
        echo "<a href=\"ru_page.php\" id=\"UserID\" name=\"UserID\">";
        $statement = "SELECT firstName FROM UserAccount WHERE userID = " . $_SESSION['userID'] . ";";
        $rs = getSQL($database, $statement);
        $name = extractSingleValue($rs);

        echo "Welcome, " . $name . "!";
    }
    else
    {
        echo "<a href=\"signon_page.php\" id=\"UserID\" name=\"UserID\">";
        echo "Log On";
    }

    echo "</a></p>";
}
?>