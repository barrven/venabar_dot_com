<?php
require '../.config.php'; // get the functions and classes library
//insert login logic here
//login_manager.php includes login_form.php, which submits login info to login_check.php
//login_check.php then redirects back to whatever page requested the login (variable stored in session
// if the login info is correct
$moduleName = 'misc_test'; //needed for login manager to redirect back to correct folder
require '../.utils/login_manager.php';

//if login succeeded, load stuff below
if (@$_SESSION['authorized'] == true){
    //set persistent page variables here for all data sources
    $page_title = 'db_test';
    $username = $_SESSION['username'];


    //todo: abstract this into dropdown menu class, then add class to Table- has tools like dropdown and nav buttons
    //if a table was set via post, then use that table
    if (isset($_POST['data-table'])){
        $selectedTable = $_POST['data-table'];
        $_SESSION['data-table'] = $selectedTable;
    }
    else{
        //otherwise, check session for a table selected
        $selectedTable = @$_SESSION['data-table'];
    }


    //html page content
    include '../.phtml/header.php';
    include '../.phtml/welcomeBanner_logoutBtn.php'
    ?>

    <div class="container bg-light border border-success rounded mt-2 p-3">
        <div class="table-responsive">
            <?php
//            if (!$db->getError()){ //check for db error before interacting
//                $table = $db->selectAll($selectedTable);
//                $col_titles = $db->getColumnNames($selectedTable);
//
//                $php_table = new Table($col_titles, $table, 'books-inventory');
//                $php_table->enablePagination(5, intval(getParam('p'))-1);
//                $php_table->draw();
//            }
//            else{
//                echo "<p class='text-danger text-center'>Could not connect to database</p>";
//            }


            $s = (@$selectedTable)? $selectedTable : 'No table selected'; //get selected table
            $db = new Database();
            if (!$db->getError()){
                $col_titles = $db->getColumnNames($selectedTable);
                //$col_titles = ['ID', 'NAME', 'ADDRESS', 'CITY', 'STATE'];

                $dropDown = new DropDownForm($s, 'Selected Table:');

                $php_table = new Table($col_titles);
                $php_table->setDataSource($db);
                $php_table->setControl($dropDown, 'SHOW TABLES in '.DB_NAME);
                $php_table->populateData();
                $php_table->enablePagination(5, (int)getParam('p')-1);
                $php_table->drawControl();
                $php_table->draw();
            }
            else{
                echo "<p class='text-danger text-center'>Could not connect to database</p>";
                $msg = $db->getErrorMessage();
                echo "<p class='text-danger text-center'>$msg</p>";
            }


            ?>
        </div>
    </div>



    <?php
    include '../.phtml/footer.php';
} //ending bracket for the if statement that checks if authorized is true

