<!-- 
    Group 2A - CSCI 467 Spring 2025
    signout_page.php - This page is created for logged in users to sign out
        of their account. User is redirected to the sign on page
-->
<?php
    session_start();
    unset($_SESSION);
    session_destroy();
    header("Location: signon_page.php");
    die;
?>
