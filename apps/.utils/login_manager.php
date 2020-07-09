<?php
session_start();
if (@$_SESSION['authorized'] == false){
    //store page that's requesting login
    $_SESSION['page_requested_login'] = '../'.$moduleName;
    //load login form if not authorized
    //form action is set to .utils/login_check.php
    //login check redirects to page_requested_login on success
    require '../.phtml/login_form.php';
}

if (getParam('action') == 'logout'){
//        $_SESSION['authorized'] = null;
//        $_SESSION['username'] = null;
    session_destroy();
    header('refresh: 0');
    header('location: ?');
}