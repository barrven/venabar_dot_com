<?php
//get the functions and classes library
require '../.config.php';

//page variables
$moduleName = 'store';
$page_title = 'Store';
$msg = 'The store is still under construction';

//insert login logic here
require '../.utils/login_manager.php';


if (@$_SESSION['authorized'] == true) {

    //html page content
    include '../.phtml/header.php';
    include '../.utils/msg.phtml';
    include '../.phtml/footer.php';
}
