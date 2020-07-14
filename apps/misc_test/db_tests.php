<?php
require '../.config.php'; // get the functions and classes library
//insert login logic here
//login_manager.php includes login_form.php, which submits login info to login_check.php
//login_check.php then redirects back to whatever page requested the login (variable stored in session
// if the login info is correct
$moduleName = 'db_test';
require '../.utils/login_manager.php';

if (@$_SESSION['authorized'] == true){

    $page_title = 'db_test';
    $msg = 'The store is still under construction';
    //html page content
    include '../.phtml/header.php';
    //include '../.utils/msg.phtml';
    $username = $_SESSION['username'];
    $db = new Database();
    //if login succeeded, load stuff below
    ?>

    <div class="text-center text-white bg-success border border-success rounded p-3">
        <!-- logout button uses action param to tell this page to logout-->
        <a class="btn btn-primary float-right" href="?action=logout">Logout</a>
        <h1>
            <span class="fas fa-robot"></span>
            <?php echo 'Welcome, ' . @$username . '!'; ?>
            <span class="fas fa-space-shuttle"></span>
        </h1>

    </div>

    <div class="container bg-light border border-success rounded mt-2 p-3">
        <?php
        $db->setQuery('SHOW TABLES in '.DB_NAME);
        $tablesList = $db->getRecord();
        $selectedTable = @$_POST['data-table'];
        ?>

        <form method="post" action="db_tests.php" style="max-width: 500px; margin: auto">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="data-table">Selected Table:</label>
                </div>
                <select class="custom-select" id="data-table" name="data-table">
                    <option selected><?php echo (@$selectedTable)? $selectedTable : 'No table selected' ?></option>
<!--                    <option selected>No Table Selected</option>-->
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
            $table = $db->selectAll($selectedTable);
            $col_titles = $db->getColumnNames($selectedTable);
            //$db->setQuery('select books_name, books_author from books');
            //$table = $db->getRecords();
            //$col_titles = ['Book Title', 'Author'];
            $php_table = new Table($col_titles, $table, 'books-inventory');
            $php_table->enablePagination(10, intval(getParam('p'))-1);
            $php_table->draw();

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

        var_dump(!empty($selectedTable));
        echo '<br>';
        var_dump($selectedTable);
        ?>
    </div>








    <?php
    include '../.phtml/footer.php';
} //ending bracket for the if statement that checks if authorized is true

