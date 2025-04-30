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

//setLogOnAttributeValue() - called in <nav> and adds elements based on a user's log-in status, such as 
//redirecting to the returning user page, or the log-in page
//Inputs -
    //$database - the PDO object with a valid database connected, passed to another function
//Output - Adds nav elements based on log-in status
function setLogOnAttributeValue($database)
{
    if($_SESSION['logged_in'] == true)
    {
        echo "<a href=\"ru_page.php\" id=\"UserID\" name=\"UserID\">";
        
        $statement = "SELECT firstName FROM UserAccount WHERE userID = " . $_SESSION['userID'] . ";";
        $rs = getSQL($database, $statement);
        $name = extractSingleValue($rs);

        echo $name . " (Account)";
        echo "</a>";
        echo "<a href=\"signout_page.php\">Log out";
        
    }
    else
    {
        echo "<a href=\"signon_page.php\" id=\"UserID\" name=\"UserID\">";
        echo "Login/Sign Up";
    }

    echo "</a>";
}
?>