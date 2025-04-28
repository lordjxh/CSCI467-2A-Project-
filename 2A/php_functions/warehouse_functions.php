<?php
//Group 2A - CSCI467 Spring 2025
//warehouse_functions - a PHP file for all functions used for warehouse employee pages. Primarily used for inventory_management.php, and 
//requires database_functions.php as a dependency.

//getInventoryItems() - fetches inventory items based on search criteria. Can be the leagacy database number, or keywords in the description.
//Result is turned into an array for use with other functions in file
//inputs -
    //$database - the database $pdo initialized
    //$legacyDB - a second PDO object with a valid connection to the legacy database
    //$searchRequest - either an identifier or keyword, used to fetch items from legacy database
    //$isNumber - tells function what search request is being used to set up SQL statement
//output - $inventoryItems - an array of items that reference both the legacyDB and 'Products' table
function getInventoryItems($database, $legacyDB, $searchRequest, $isNumber)
{
    $index = 0; //holds index position

    //Step 1 - Pull information from legacy database based on search term
    if($isNumber == false)
    {
        $statement = "SELECT * FROM parts WHERE description LIKE \"%" . $searchRequest . "%\";";
    }
    else
    {
        $statement = "SELECT * FROM parts WHERE number = " . $searchRequest . ";";
    }

    $rs = getSQL($legacyDB, $statement);

    //Step 2 - Create array based on queried items
    foreach ($rs as $item)
    {
        $inventoryItems[$index]['legacyID'] = $item['number'];
        $inventoryItems[$index]['description'] = $item['description'];
        $inventoryItems[$index]['pictureURL'] = $item['pictureURL'];

        //Step 3 - Match to retrieve the Product ID from 'Products' table
        $statement2 = "SELECT * FROM Products WHERE legacyID = " . $inventoryItems[$index]['legacyID'] . ";";
        $rs2 = getSQL($database, $statement2);
        
        foreach($rs2 as $item2)
        {
            $inventoryItems[$index]['productID'] = $item2['productID'];
            $inventoryItems[$index]['storeQuantity'] = $item2['storeQuantity'];
            $inventoryItems[$index]['warehouseQuantity'] = $item2['warehouseQuantity'];
        }

        $index++; //increments index by one
    }

    //if at least one item was found, return the array
    if($index > 0)
    {
        return $inventoryItems;
    }
    else //else return null
    {
        return NULL;
    }
}

//validateInventoryQuantities() - determines whether entered quantities are valid; never go below zero, and returns result
//inputs -
    //$newStoreQuantity - the amount that will be added to the store quantity
    //$newWarehouseQuantity - the amount that will be added to the warehouse quantity
    //$storeQuantity - the original amount of the store quantity
    //$warehouseQuantity - the original amount of the warehouse quantity
//output - boolean - true if valid quantity to update, false otherwise
function validateInventoryQuantities($newStoreQuantity, $newWarehouseQuantity, $storeQuantity, $warehouseQuantity)
{
    $storeCalculation = $storeQuantity + $newStoreQuantity;
    $warehouseCalculation = $warehouseQuantity + $newWarehouseQuantity;

    if($storeCalculation < 0)
    {
        return false;
    }

    if($warehouseCalculation < 0)
    {
        return false;
    }

    return true;
}

//setInventoryItems() - when called, the values for the item specified will be updated in the 'Products' table
//inputs -
    //$database - the database $pdo initialized
    //$productID - the primary key for the product being updated
    //$newStoreQuantity - the amount that will be added to the store quantity
    //$newWarehouseQuantity - the amount that will be added to the warehouse quantity
    //$storeQuantity - the original amount of the store quantity
    //$warehouseQuantity - the original amount of the warehouse quantity
//output - updates the 'Products' table with new quantities for the item specified
function setInventoryItems($database, $productID, $storeQuantity, $warehouseQuantity, $newStoreQuantity, $newWarehouseQuantity)
{
    $storeCalculation = $storeQuantity + $newStoreQuantity;
    $warehouseCalculation = $warehouseQuantity + $newWarehouseQuantity;

    $statement = "UPDATE Products SET storeQuantity = " . $storeCalculation . ", warehouseQuantity = " . $warehouseCalculation . " WHERE productID = " . $productID . ";";
    updateDatabaseValue($database, $statement);
}

//printInventoryItems() - prints all the items stored in the array of items fetched from the search
//inputs -
    //$inventoryItems - an array created from getInventoryItems()
//output - prints to HTML a table with form inputs for each item found
function printInventoryItems($inventoryItems)
{
    echo "<table>";
    echo "<tr><td>Modify</td><td/><td/><td>Store Quantity</td><td>Warehouse Quantity</td><td>New Store Quantity</td><td>New Warehouse Quantity</td></tr>";
    
    foreach($inventoryItems as $item)
    {
        echo "<tr>";
        echo "<td class=\"item-selection\"><input type=\"checkbox\" name=\"productSelection[]\" value=" . $item['productID'] . ">";
        echo "<td class=\"item-image\"><img src=\"" . $item['pictureURL'] . "\" /></td>";
        echo "<td class=\"item-description\">" . $item['description'] . "</td>";
        echo "<td class=\"item-storeQuantity\">" . $item['storeQuantity'] . "</td>";
        echo "<td class=\"item-warehouseQuantity\">" . $item['warehouseQuantity'] . "</td>";

        echo "<input type=\"hidden\" id=\"storeQuantity_" . $item['productID'] . "\" name=\"storeQuantity_" . $item['productID'] . "\" value=\"" . $item['storeQuantity'] . "\">";
        echo "<input type=\"hidden\" id=\"warehouseQuantity_" . $item['productID'] . "\" name=\"warehouseQuantity_" . $item['productID'] . "\" value=\"" . $item['warehouseQuantity'] . "\">";

        echo "<td class=\"input-storeQuantity\"><input type=\"number\" id=\"inputStoreQuantity_" . $item['productID'] . "\" name=\"inputStoreQuantity_" . $item['productID'] . "\" value=0></input></td>";
        echo "<td class=\"input-warehouseQuantity\"><input type=\"number\" id=\"inputWarehouseQuantity_" . $item['productID'] . "\" name=\"inputWarehouseQuantity_" . $item['productID'] . "\"value=0></input></td>";

        echo "</tr>";
    }

    echo "</table>";
}

?>