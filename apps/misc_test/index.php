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
    $db = new Database();

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

    <!--todo: abstract this dropdown menu into a class so it's reusable-->
    <div class="container bg-light border border-success rounded mt-2 p-3">
        <?php
        if (!$db->getError()){ //check for db error before interacting
            $db->setQuery('SHOW TABLES in '.DB_NAME);
            $tablesList = $db->getRecord();
        }
        else{
            echo "<p class='text-danger text-center'>Could not connect to database</p>";
        }
        ?>

        <form method="post" action="" style="max-width: 500px; margin: auto">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="data-table">Selected Table:</label>
                </div>
                <select class="custom-select" id="data-table" name="data-table">
                    <option selected><?php echo (@$selectedTable)? $selectedTable : 'No table selected' ?></option>
                    <?php
                    foreach ($tablesList as $item){
                        echo "<option value='$item'>$item</option>".PHP_EOL;
                    }
                    ?>
                </select>
                <div class="input-group-append">
                    <button class="btn btn-success" type="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>


    <div class="container bg-light border border-success rounded mt-2 p-3">
        <div class="table-responsive">
            <?php
            if (!$db->getError()){ //check for db error before interacting
                $table = $db->selectAll($selectedTable);
                $col_titles = $db->getColumnNames($selectedTable);
                $php_table = new Table($col_titles, $table, 'books-inventory');
                $php_table->enablePagination(5, intval(getParam('p'))-1);
                $php_table->draw();
            }
            else{
                echo "<p class='text-danger text-center'>Could not connect to database</p>";
            }
            ?>
        </div>
    </div>

    <div class="container bg-light border border-success rounded mt-2 p-3">
        <?php
//            echo $php_table->numPages.'<br>';
//            echo $php_table->currPageNum.'<br>';
//            $x = getParam('p');
//            var_dump($x);
//            echo '<br>';
//            var_dump(intval($x));

//        echo 'selected table empty: ';
//        var_dump(empty($selectedTable));
//
//        echo '<br>selected table contents: ';
//        var_dump($selectedTable);
//
//        echo '<br>Session data-table: ';
//        var_dump($_SESSION['data-table']);
//
//        echo '<br>datatable set on post: ';
//        var_dump(isset($_POST['data-table']));
//
//        echo '<br>Current table page: ';
//        var_dump($php_table->currPageNum);
        ?>
    </div>








    <?php
    include '../.phtml/footer.php';
} //ending bracket for the if statement that checks if authorized is true

