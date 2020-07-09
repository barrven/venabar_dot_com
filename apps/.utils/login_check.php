<?php
$db = new Database();
session_start();
$username = getParam('username');
$pwd = md5(getParam('pwd'));
if(checkPassword($username, $pwd, $db)){
    if (getParam('action') == 'logout'){
        session_destroy();
        //todo: figure out how else to redirect to the login form
        header('location: login_form.php');
    }
    $_SESSION['authorized'] = true;
    $_SESSION['username'] = $username;
}
else{
    //todo: same as above
    //redirect to login page if login is not valid
    header('location: ?action=redirect');
}
