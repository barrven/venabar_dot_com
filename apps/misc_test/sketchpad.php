<?php
require '../.config.php';

include '../.phtml/header.php';
?>


<div class="container bg-light border border-success rounded mt-5 p-3">
    <?php
    $db = new Database();
    $cols = ['books_name', 'books_year_published'];
    $vals = ['Lord of the Rings: Return of the King', 1948];
    //$x = $db->updateById('books', 102, 'books_id', $cols, $vals);

    $insCols = ['books_name', 'books_author', 'books_year_published', 'publishers_id', 'genres_id'];
    $insVals = ['The Hobbit', 'JRR Tolkien', 1945, 1003, 3];
    //$x = $db->insert($insCols, $insVals, 'books');
    //var_dump($x);
    ?>
</div>



<div class="container bg-light border border-success rounded mt-5 p-3">
    <?php
    $db = new Database();
    $colHeadings = $db->getColumnNames('books');
    array_push($colHeadings, 'Buttons');
    $t = new Table($colHeadings);
    $t->setDataSource($db, 'select * from books');
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

<div class="container bg-light border border-success rounded mt-5 p-3">
    <?php
    //$db->setQuery('select * from publishers where publishers_state = \'NY\'');
    //$record = $db->getRecord();
    $t2 = new Table(['ID', 'NAME', 'ADDRESS', 'CITY', 'STATE', 'COUNTRY']);
    $t2->setDataSource($db, 'select * from publishers where publishers_country = \'USA\'');
    $t2->draw();
    ?>
</div>


<?php
include '../.phtml/footer.php';
