<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/signon_page.css">
        <title>Login</title>
    </head>
    <body>
        <header>
            <div>
                <a href="main_page.php">Home</a>
                <a href="esignon_page.php">Employee Login</a>
                <a href="cart.php">Cart</a>
            </div>
        </header>
        <main>
            <h1>Create an account</h1>
            <!-- User Sign-up Data Entry -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="firstName">First name:</label>
                <input type="text" id="firstName" name="firstName" required>
                <br>
                <label for="last">Last name:</label>
                <input type="text" id="lastName" name="lastName" required>
                <br>
                <label for="address">Shipping address:</label>
                <input type="text" id="address" name="address" required>
                <br>
                <label for="state">State:</label>
                <input type="text" id="state" name="state" required>
                <br>
                <label for="zipcode">Zip code:</label>
                <input type="text" id="zipcode" name="zipcode" required>
                <br>
                <label for="phoneNum">Phone number:</label>
                <input type="text" id="phoneNum" name="phoneNum" required>
                <br>
                <label for="signup__email">Email:</label>
                <input type="email" id="signup_email" name="signup_email" required>
                <br>
                <label for="signup_password">Password:</label>
                <input type="password" id="signup_password" name="signup_password" required>
                <br>
                <button type="submit" name="signup">Sign up</button>
            </form>
            <!-- if a user already has an account, this provides a way back to the log in page -->
            <a href="signon_page.php">Have an account? Log in here!</a>
            <br>
            <?php
                session_start();
                include "secrets.php";
                include "database_functions.php";

                /*connect to database*/
                $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

                /*signs up a user if there data is not already in the database*/
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $email = isset($_POST['signup_email']) ? trim($_POST['signup_email']) : '';
                    $first = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
                    $last = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
                    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
                    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
                    $zipcode = isset($_POST['zipcode']) ? trim($_POST['zipcode']) : '';
                    $phoneNum = isset($_POST['phoneNum']) ? trim($_POST['phoneNum']) : '';
                    $password = isset($_POST['signup_password']) ? trim($_POST['signup_password']) : '';

                    $sql = $database->prepare("SELECT * FROM UserAccount WHERE email = :email");
                    if(!$sql) {
                        die("Error with SQL Query: " . $database->error);
                    }

                    $sql->bindParam(':email', $email, PDO::PARAM_STR);
                    $sql->execute();
                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                    /*if email is not already registered in the database, then user's data is stored*/
                    if(!$result) {
                        $insert=$database->prepare("INSERT INTO UserAccount(firstName, lastName, shippingAddress, state, zipcode, phone, email, userPassword) VALUES (:firstName, :lastName,>
                        $insert->bindParam(':firstName', $first, PDO::PARAM_STR);
                        $insert->bindParam(':lastName', $last, PDO::PARAM_STR);
                        $insert->bindParam(':shippingAddress', $address, PDO::PARAM_STR);
                        $insert->bindParam(':state', $state, PDO::PARAM_STR);
                        $insert->bindParam(':zipcode', $zipcode, PDO::PARAM_STR);
                        $insert->bindParam(':phone', $phoneNum, PDO::PARAM_STR);
                        $insert->bindParam(':email', $email, PDO::PARAM_STR);
                        $insert->bindParam(':userPassword', $password, PDO::PARAM_STR);

                        /*user is logged in with their new account and 'logged_in' and 'userID' are set*/
                        if($insert->execute()) {
                            session_regenerate_id(true);
                            $_SESSION['logged_in'] = true;
                            $_SESSION['userID'] = $database->lastInsertId();
                            header("Location: ru_page.php");
                            exit();
                        } else {
                            echo "Something went wrong, please try again!";
                        }
                    /*else user is told to log in using their email*/
                    } else {
                        echo "An account with that email is already registered, please log in.";
                    }
                }
            ?>
        </main>
    </body>
</html>
