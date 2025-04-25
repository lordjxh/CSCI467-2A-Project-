<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/signon_page.css">
        <title>Login</title>
    </head>
    <body>
        <header>
            <nav>
                <a href="main_page.php">Home</a>
                <a href="esignon_page.php">Employee Login</a>
            </nav>
        </header>
        <main>
            <h2>Login</h2>
            <!--User Login Data Entry -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="login_email">Email:</label>
                <input type="email" id="login_email" name="login_email" required>
                <br>
                <label for="login_password">Password:</label>
                <input type="password" id="login_password" name="login_password" required>
                <br>
                <button type="submit" name="login">Login</button>
            </form>

            <a href="signup_page.php">Don't have an account? Sign up here!</a>
            
            <?php
                session_start();

                include "secrets.php";
                include "database_functions.php";

                /*connect to database*/
                $database = establishDB($databaseHost, $databaseUsername, $databasePassword);

                /*searches for user data in database and signs user in if data is found*/
                if ($_SERVER["REQUEST_METHOD"] == "POST") {

                    $email = isset($_POST['login_email']) ? trim($_POST['login_email']) : '';
                    $password = isset($_POST['login_password']) ? trim($_POST['login_password']) : '';

                    $sql = $database->prepare("SELECT * FROM UserAccount WHERE email = :email AND userPassword = :password");
                    if(!$sql) {
                        die("Error with SQL Query: " . $database->error);
                    }

                    $sql->bindParam(':email', $email, PDO::PARAM_STR);
                    $sql->bindParam(':password', $password, PDO::PARAM_STR);
                    $sql->execute();
                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                    /*if email and password are found in a table entry, then go to ru_page.php*/
                    if($result) {

                        session_regenerate_id(true);

                        /*logged_in is set to true so other pages know not to display
                          log-in/sign-up prompt and will show log-out button*/
                        $_SESSION['logged_in'] = true;
                        $_SESSION['userID'] = $result['userID'];

                        header("Location: ru_page.php?userID=" . urlencode($result['userID']));
                        exit();
                    }
                
                    echo "Invalid email or password, please try again!";
                    $sql->close();
                }
            ?>
        </main>
    </body>
</html>
