<?php
//Group 2A - CSCI467 Spring 2025
//database_functions - a PHP file for all functions that can work with the PDO object and databases. Serves as an
//alternative use case to clean up code on individual pages.


//establishDB() - creates a $PDO object conection to a database server
//inputs -
    //$dsn - the address of the database to connect to
    //$username - the username to connect
    //$password - the password to connect
//output - $pdo object upon successful connection, false otherwise
function establishDB($dsn, $username, $password)
{
    //establish connection to MariaDB and set PDO object
    try{ //if something goes wrong, an exception is thrown below
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    catch(PDOexception $error){ //handles exception(s)
        http_response_code(401);
        die("Connection to DB failed: " . $error->getMessage());
    }
}

//getSQL() - takes a SQL statement and returns rows retrieved
//inputs -
    //$pdo - the connected database to query
    //$statement - a prepared SQL SELECT statement
//output - $rs - the array of queried content(s) before separation by row
function getSQL($pdo, $statement)
{
    $rs = $pdo->query($statement);
    return $rs;
}

//insertDatabaseValue() - adds an entry to a database table
//inputs -
    //$pdo - the connected database to query
    //$statement - a prepared SQL INSERT INTO statement
//output - none, stops HTML if insertion fails
function insertDatabaseValue($pdo, $statement)
{
    try
    {
        $output = $pdo->prepare($statement);
        $output->execute();
    }
    catch (PDOException $error) 
    {
        http_response_code(401);
        die("Database insertion failed: " . $error->getMessage());
    }
}

//deleteDatabaseValue() - removes values from a database table
//inputs -
    //$pdo - the connected database to query
    //$statement - a prepared SQL INSERT INTO statement
//output - none, stops HTML if deletion fails
function deleteDatabaseValue($pdo, $statement)
{
    try
    {
        $output = $pdo->prepare($statement);
        $output->execute();
    }
    catch (PDOException $error) 
    {
        http_response_code(401);
        die("Database deletion failed: " . $error->getMessage());
    }
}

//updateDatabaseValue() - updates an entry to a database table
//inputs -
    //$pdo - the connected database to query
    //$statement - a prepared SQL UPDATE statement
//output - none, returns error message on update fail
function updateDatabaseValue($pdo, $statement)
{
    try
    {
        $pdo->query($statement);
    }
    catch(PDOException $error)
    {
        http_response_code(401);
        die("Database update failed: " . $error->getMessage());
    }
}

//extractSingleValue() - for SQL queries looking for a single value, returns the value
//inputs -
    //$rs - the array of queried content(s) before separation by row
//output - value contained within the query
function extractSingleValue($rs)
{
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    { 
        foreach($row as $key=>$value)
        {
            return $value;
        }
    }

    //if this point is reached, the value failed to extract, so return null
    return null;
}

//debugPrintRows() - for debugging, will print all rows from a SQL query array
//inputs -
    //$rs - the array of queried content(s) before separation by row
//output - HTML text to output
function debugPrintRows($rs)
{
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        echo "<br>";
        foreach($row as $key=>$value)
        {
            echo $value;
            echo " ";
        }

        echo "</br>";
    }
}

//loadLegacyProducts() - populates the Products table with items from the legacy
//database if they do not exist already. Sets default quantity to zero.
//inputs -
    //$currentDB - the new database that holds new attributes
    //$legacyDB - the legacy database to retrive items from
//output - the 'Products' table will be amended with new entries, and a result variable is returned
function loadLegacyProducts($currentDB, $legacyDB)
{
    //statement to get legacy part ID's
    $statement = "SELECT number FROM parts;";
    $rs = getSQL($legacyDB, $statement);

    $existingEntries = 0;
    $newEntries = 0;

    //while-loop to iterate through each item
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        foreach($row as $key=>$value)
        {
            //determine new entry does not yet exist
            $existing = "SELECT legacyID from Products WHERE legacyID = " . $value . ";";
            $rs2 = getSQL($currentDB, $existing);
            $row2 = $rs2->fetch(PDO::FETCH_ASSOC);

            if(!$row2) //add if it does not exist
            {
                $entry = "INSERT INTO Products (storeQuantity, warehouseQuantity, legacyID) VALUES (0, 0, " . $value . ");";
                insertDatabaseValue($currentDB, $entry);
                $newEntries++;
            }
            else
            {
                $existingEntries++;
            }
        }
    }

    if($newEntries > 0)
    {
        $result = "Successfully added " . $newEntries . " based on " . $existingEntries . " existing entries.";
    }
    else
    {
        $result = "No new entries to add from legacy database.";
    }

    return $result;
}
?>