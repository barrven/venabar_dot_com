<?php
require '../.config.php';
$moduleName = 'db_test';
$page_title = $moduleName;
include '../.phtml/header.php';
$db = new Database();
?>

    <div class="container text-primary bg-light border border-success rounded mt-2 p-3">
        <?php
        $db->setQuery('select * from bookss');
        $res = $db->getRecords();
        var_dump($res);


        echo '<hr>';
        echo $db->getError();

        ?>
    </div>

    <div class="container text-primary bg-light border border-success rounded mt-2 p-3">
        <?php
        $db->setQuery('select * from genres');
        $res = $db->getRecords();
        var_dump($res);
        echo '<hr>';
        var_dump($db->getError());

        ?>
    </div>







<?php
include '../.phtml/footer.php';