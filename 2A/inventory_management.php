<!-- 
    Group 2A - CSCI 467 Spring 2025
    inventory_management.php - This page is created for warehouse employees to manage inventory levels between site-scope and warehouse-scope. When new
        items are received, they can be updated. Additionally, it allows for managing the inventory should issues occur with automatic reduction from
        functionality throughout the entire front-end. Employees can search by the legacy ID or by keywords to find their items, then select the item(s)
        which need updating. Includes validation to prevent negative values.

-->

<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/warehouse_functions.php";

    session_start();
                
    //establish connection(s) to database(s)
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
    
    $inventoryResult = NULL; //creates a variable to hold result of updating inventory

    if ($_SERVER['REQUEST_METHOD'] == 'POST') //if a form was submitted, proceed...
    {
        if (isset($_POST['modify'])) //if modify was pressed, start extracting $_POST values and updating 'Products' table
        {
            $count = 0; //count for amount of successful updated entries

            foreach($_POST['productSelection'] as $item) //for each item that was selected, proceed...
            {
                //get variables called from $_POST
                $productID = $item;
                $storeQuantity = $_POST['storeQuantity_' . $item];
                $warehouseQuantity = $_POST['warehouseQuantity_' . $item];
                $newStoreQuantity = $_POST['inputStoreQuantity_' . $item];
                $newWarehouseQuantity = $_POST['inputWarehouseQuantity_' . $item];

                //if the amounts are valid, then call setInventoryItems() to store the new quantities
                if(validateInventoryQuantities($newStoreQuantity, $newWarehouseQuantity, $storeQuantity, $warehouseQuantity))
                {
                    setInventoryItems($database, $productID, $storeQuantity, $warehouseQuantity, $newStoreQuantity, $newWarehouseQuantity);
                    $count++;
                }
            }

            $status = "Updated " . $count . " items successfully.";
        }
        else if (isset($_POST['search'])) //else if search was pressed, start search call
        {
            if($_POST['searchID'] != "") //if an ID was entered, search by ID
            {
                $inventoryResult = getInventoryItems($database, $legacyDB, $_POST['searchID'], true);
            }
            else if($_POST['searchTerm'] != "") //else if a keyword as entered, search by keyword
            {
                $inventoryResult = getInventoryItems($database, $legacyDB, $_POST['searchTerm'], false);
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
        <link rel="stylesheet" href="css/inventory_management.css">
        <title>Inventory Management</title>
    </head>
    <body>
        <header>
            <nav>
                <a href="admin_page.php" class="nav-button"><- Go Back</a>
            </nav>
        </header>
        <div style="margin-top: 30px;"></div>
            <div class="left">
                <div class="section">
                    <form id="regForm" method="post">
                        <input id="searchID" name="searchID" placeholder="Legacy Num..."></input>
                        <input id="searchTerm" name="searchTerm" placeholder="Description..."></input>
                        <button type="submit" id="search" name="search" class="button">Search</button>
                        <?php
                            if ($_SERVER['REQUEST_METHOD'] == 'POST')
                            {
                                if(isset($_POST['modify'])) //if modify was pressed, print status
                                {
                                    echo "<p>" . $status . "</p>";
                                }

                                if(isset($_POST['search'])) //if search was pressed, print the modify button and search results
                                {
                                    if($inventoryResult != NULL)
                                    {
                                        echo "<button type=\"submit\" id=\"modify\" name=\"modify\" class=\"button\">Modify</button>";
                                        printInventoryItems($inventoryResult); 
                                    }
                                    else //else print an error message
                                    {
                                        echo "<p>Search returned zero results. Please try again.</p>";
                                    }
                                }
                            }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>