<?php

function getInventoryItems($database, $legacyDB, $searchTerm)
{
    $index = 0;

    //Step 1 - Pull information from legacy database based on search term
    $statement = "SELECT * FROM parts WHERE description LIKE \"%" . $searchTerm . "%\";";
    $rs = getSQL($legacyDB, $statement);

    //Step 2 - Create array based on queried items
    foreach ($rs as $item)
    {
        $output[$index]['legacyID'] = $item['number'];
        $output[$index]['description'] = $item['description'];
        $output[$index]['pictureURL'] = $item['pictureURL'];

        //Step 3 - Match to retrieve the Product ID from 'Products' table
        $statement2 = "SELECT * FROM Products WHERE legacyID = " . $output[$index]['legacyID'] . ";";
        $rs2 = getSQL($database, $statement2);
        
        foreach($rs2 as $item2)
        {
            $output[$index]['productID'] = $item2['productID'];
            $output[$index]['storeQuantity'] = $item2['storeQuantity'];
            $output[$index]['warehouseQuantity'] = $item2['warehouseQuantity'];
        }

        $index++;
    }

    if($index > 0)
    {
        return $output;
    }
    else
    {
        return NULL;
    }
}

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

function setInventoryItems($database, $productID, $storeQuantity, $warehouseQuantity, $newStoreQuantity, $newWarehouseQuantity)
{
    $storeCalculation = $storeQuantity + $newStoreQuantity;
    $warehouseCalculation = $warehouseQuantity + $newWarehouseQuantity;

    $statement = "UPDATE Products SET storeQuantity = " . $storeCalculation . ", warehouseQuantity = " . $warehouseCalculation . " WHERE productID = " . $productID . ";";
    insertDatabaseValue($database, $statement);
}

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

        echo "<td class=\"input-storeQuantity\"><input type=\"number\" id=\"inputStoreQuantity_" . $item['productID'] . "\" name=\"inputStoreQuantity_" . $item['productID'] . "\"></input></td>";
        echo "<td class=\"input-warehouseQuantity\"><input type=\"number\" id=\"inputWarehouseQuantity_" . $item['productID'] . "\" name=\"inputWarehouseQuantity_" . $item['productID'] . "\"></input></td>";

        echo "</tr>";
    }

    echo "</table>";
}

?>