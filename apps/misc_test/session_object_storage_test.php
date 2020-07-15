<?php
require '../.config.php';

include '../.phtml/header.php';
?>

<div class="container bg-light border border-success rounded m-5 p-3">
    <?php
    $db = new Database();
    $table = new Table([], [[]]);
    $table->setDataSource($db, 'select * from genres');
    $data = $table->data;
    foreach ($data as $item){
        echo sizeof($item).' : ' ;
        foreach ($item as $value){
            var_dump($value);
            echo ' | ';
        }
        echo '<br>';
    }

    $table->draw();

    echo '<hr>';




    ?>
</div>




<?php
include '../.phtml/footer.php';
