<html>
    <head>
        <?php
            include "secrets.php";
            include "php_functions/database_functions.php";
                
            $legacyDB = establishDB($legacyHost, $legacyUsername, $legacyPassword);
            $database = establishDB($databaseHost, $databaseUsername, $databasePassword);
        ?>
    </head>
    <body>
        <p>Checkout was successful</p>
    </body>
</html>
