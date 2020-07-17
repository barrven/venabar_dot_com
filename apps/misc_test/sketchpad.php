<?php
require '../.config.php';

include '../.phtml/header.php';
?>

<div class="container bg-light border border-success rounded m-5 p-3">
    <?php
    $db = new Database();
//    $table = new Table([], [[]]);
//    $table->setDataSource($db, 'select * from genres');
//    $data = $table->data;
//    foreach ($data as $item){
//        echo sizeof($item).' : ' ;
//        foreach ($item as $value){
//            var_dump($value);
//            echo ' | ';
//        }
//        echo '<br>';
//    }
//
//    $table->draw();
//
//    echo '<hr>';

    //$db->setQuery('select * from books where books_id = 101');
   // $db->setQuery('show tables in '.DB_NAME);
    //$db->setQuery('select books_author from books');
    //$test = $db->getRecord(PDO::FETCH_ASSOC);
    //var_dump($test);
    $t = new Table();
    $t->setDataSource($db);
    $t->populateData('select * from books');
    $newCol = [
        '<button type="button" class="btn btn-primary">Primary</button>',
        '<button type="button" class="btn btn-danger">Danger</button>',
        '<button type="button" class="btn btn-primary">Primary</button>',
        '<button type="button" class="btn btn-primary">Primary</button>'
    ];
    $t->addColumn($newCol);
    $t->draw();

    ?>
</div>

<div class="container bg-light border border-success rounded m-5 p-3">
    <?php
    //$db->setQuery('select * from publishers where publishers_state = \'NY\'');
    //$record = $db->getRecord();
    $t2 = new Table(['ID', 'NAME', 'ADDRESS', 'CITY', 'STATE', 'COUNTRY']);
    $t2->setDataSource($db);
    $t2->populateData('select * from publishers where publishers_country = \'USA\'');
    $t2->draw();
    ?>
</div>


<?php
include '../.phtml/footer.php';
