<?php
    include "secrets.php";
    include "php_functions/user_functions.php";
    include "php_functions/database_functions.php";
    include "php_functions/warehouse_functions.php";

    session_start();
                
    //establish connection(s) to database(s)
    $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
    $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
    
    $inventoryResult = NULL;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') 
    {
        if (isset($_POST['modify']))
        {
            $count = 0;

            foreach($_POST['productSelection'] as $item)
            {
                $productID = $item;
                $storeQuantity = $_POST['storeQuantity_' . $item];
                $warehouseQuantity = $_POST['warehouseQuantity_' . $item];
                $newStoreQuantity = $_POST['inputStoreQuantity_' . $item];
                $newWarehouseQuantity = $_POST['inputWarehouseQuantity_' . $item];

                if(validateInventoryQuantities($newStoreQuantity, $newWarehouseQuantity, $storeQuantity, $warehouseQuantity))
                {
                    setInventoryItems($database, $productID, $storeQuantity, $warehouseQuantity, $newStoreQuantity, $newWarehouseQuantity);
                }

                $count++;
            }

            $status = "Updated " . $count . " items successfully.";
        }
        else if (isset($_POST['search']))
        {
            $inventoryResult = getInventoryItems($database, $legacyDB, $_POST['searchTerm']);
        }
    }
?>

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
                <a href="admin_page.php" class="button"><- Go Back</a>
            </nav>
        </header>
        <div style="margin-top: 30px;"></div>
            <div class="left">
                <div class="section">
                    <form id="regForm" method="post">
                        <input id="searchTerm" name="searchTerm"></input>
                        <button type="submit" id="search" name="search">Search</button>
                        <?php
                            if ($_SERVER['REQUEST_METHOD'] == 'POST')
                            {
                                if(isset($_POST['modify']))
                                {
                                    echo "<p>" . $status . "</p>";
                                }

                                if(isset($_POST['search']))
                                {
                                    if($inventoryResult != NULL)
                                    {
                                        echo "<button type=\"submit\" id=\"modify\" name=\"modify\">Modify</button>";
                                        printInventoryItems($inventoryResult); 
                                    }
                                    else
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