<?php
require 'Database.php';
require 'functions.php';
$connected = false;
$db = new Database();
$result = false;
$num = $_GET['id'];

if (!$db->getError()){
    $connected = true;
    if (isset($_GET['action']) && isset($_GET['id'])){
        if ($_GET['action'] == 'confirm'){

            $result = $db->delete('games', $num, 'num');
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <!--bootstrap-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
    <!--icons-->
    <script src="https://kit.fontawesome.com/4467a63d2d.js" crossorigin="anonymous"></script>
    <style>
        .max-width-350{max-width: 350px;}
    </style>
    <title>Delete Game</title>
</head>
<body class="bg-dark">

<?php
if ($result){
    echo '<div class="container bg-success border border-success rounded max-width-350 mt-3">'
        .'<span class="text-center text-light">Successfully deleted!</span>'
        .'<a class=\'btn btn-primary m-3\' href=\'view.php\'>Back to View</a> '
        .'</div>';
}
//else{
//    echo "<p class='text-danger text-center'>Could not connect to database</p>";
//    $msg = $db->getErrorMessage();
//    echo "<p class='text-danger text-center'>$msg</p>";
//}
?>

<div class="container bg-light border border-success rounded mt-5 p-3">
    <h1 class="text-center mb-5">
        <?php
            if ($result){
                echo "Game #$num deleted";
            }
            else{
                echo "Delete game #$num?";
            }
        ?>
    </h1>

    <form method="post" action="delete.php?action=confirm&id=<?php echo $num?>">
        <div class="max-width-350 m-auto">
            <button type="submit" class="btn btn-danger btn-block" >Confirm Delete</button>
        </div>
    </form>

    <div class="max-width-350 m-auto">
        <a class="btn btn-primary btn-block mt-3" href="view.php">Cancel</a>
    </div>

</div>

<footer class="text-white bg-dark p-3 text-center mt-auto">
    This site <span class="far fa-copyright"></span> 2020 by Barrington Venables
</footer>
</body>
</html>
