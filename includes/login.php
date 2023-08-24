<?php session_start(); ?>
<?php global $connection; ?>
<?php include "db.php"; ?>
<?php include "../admin/includes/functions.php" ?>


<?php

    if(isset($_POST['login'])) {

        login_user($_POST['username'], $_POST['password']);

    }

?>
