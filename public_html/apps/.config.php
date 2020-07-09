<?php
//define path to all the private files and folders not accessible to the public if it's not defined already
//do this by getting the actual path from root directory, stripping any extra characters
// public_html/apps
defined('APPS_PATH') || define('APPS_PATH', realpath(dirname(__FILE__)).'/apps');
require '.lib/classes.php';
require '.lib/functions.php';

//define all constants here for use across other files
define('DB_HOST', 'localhost');
define('DB_USER', 'barringt_librarian');
define('DB_PASS', 'someSuperStrongPw');
define('DB_NAME', 'barringt_book_library');