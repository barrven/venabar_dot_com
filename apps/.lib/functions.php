<?php
function getParam($name, $default = '')
{
    if (isset($_REQUEST[$name])) {
        return $_REQUEST[$name];
    } else {
        return $default;
    }
}

function checkPassword($username, $password, Database $database){
    $error = $database->getError(); //$error is null if there was no problem connecting
    if (!$error){
        $checkVal = $database->selectRecordWhere('password', 'users', 'username', $username);
        if ($password == md5($checkVal)){
            return true;
        }
    }
    return false;
}

