<?php
require_once '../.config.php';
session_start();
$db = new Database();
$username = getParam('username');
$pwd = md5(getParam('pwd'));
if(checkPassword($username, $pwd, $db)){
    //login succeeded
    $_SESSION['authorized'] = true;
    $_SESSION['username'] = $username;
}
else{
    //track a failed attempt
    $_SESSION['login_attempted'] = true;
}
//redirect to login page if login is not valid
header('location: '.$_SESSION['page_requested_login']);
exit();

