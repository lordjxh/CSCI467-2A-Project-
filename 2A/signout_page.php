<?php
    session_start();
    unset($_SESSION);
    session_destroy();
    header("Location: signon_page.php");
    die;
?>
