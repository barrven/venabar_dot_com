<?php
include '../.phtml/header.php';
?>

<body class="bg-dark">
    <div class="text-center text-white bg-success border border-success rounded p-3">
        <a class="btn btn-primary float-right" href="login_test.php?action=logout">Logout</a>
        <h1>
            <span class="fas fa-robot"></span>
            <?php echo 'Welcome, ' . $username . '!'; ?>
            <span class="fas fa-space-shuttle"></span>
        </h1>

    </div>

    <div class="container text-primary bg-light border border-success rounded mt-2 p-3">
        <p>
            <?php
                $query = 'SELECT books_name, publishers_name 
                            FROM books b JOIN publishers p 
                            ON b.publishers_id = p.publishers_id;';
                $db->setQuery($query);
                $result = $db->getRecords();
                print_r($result);
            ?>
        </p>
    </div>

    <div class="container text-primary bg-light border border-success rounded mt-2 p-3">
        <p>
            <?php
            $query = 'SELECT books_name, publishers_name 
                            FROM books b JOIN publishers p 
                            ON b.publishers_id = p.publishers_id;';
            $db->setQuery($query);
            $result = $db->getRecords(PDO::FETCH_ASSOC);
            print_r($result);
            ?>
        </p>
    </div>

    <div class="container text-primary bg-light border border-success rounded mt-2 p-3">
        <p>
            <?php
                $x = $db->selectRecordWhere('password', 'users', 'username', 'barrington');
                var_dump($x);
            ?>
        </p>
        <p class="text-center">
            <a class="btn btn-success" href="misc_test.php">Goto Misc Test</a>
        </p>
    </div>

    <div class="container text-primary bg-light border border-success rounded mt-2 p-3">
        <p>
            <?php
//                $table = $db->selectAll('books', PDO::FETCH_ASSOC);
//                var_dump($table);
//                echo "<p>$db->error</p>";
                $cols = ['books_name', 'books_author', 'books_year_published', 'publishers_id', 'genres_id'];
                $vals = ['2001: A Space Odyssey', 'Arthur C Clarke', 1961, 1001, 2];
                $result = $db->insert($cols, $vals, 'booksss');
                if (!$result){
                    echo '<h4 class="text-danger">There was a database error</h4>';
                    $e = $db->getErrorMessage();
                    echo "<p>$e</p>";
                }

            ?>
        </p>
    </div>
<?php include '../.phtml/footer.php';