<?php
require 'Database.php';
require 'functions.php';
$connected = false;
$db = new Database();
$num = 'null';
$date = 'null';
$data = ['','','','','','','','','',''];
$result = false;

if (!$db->getError()){
    $connected = true;
    if(isset($_GET['id'])){
        $data = $db->selectRecordWhere('games', 'num', $_GET['id']);
        $num = $_GET['id'];
        $date = $data[1];
    }

    if (isset($_GET['action']) && isset($_GET['id'])){

        if ($_GET['action'] == 'submit'){
            $id = $_GET['id'];

            $p1Score = calcScore(trim(intval($_POST['p1-bid'])), trim(intval($_POST['p1-wins'])));
            $p2Score = calcScore(trim(intval($_POST['p2-bid'])), trim(intval($_POST['p2-wins'])));

            $columns = [
                'p1_name', 'p2_name',
                'p1_bid', 'p2_bid',
                'p1_win', 'p2_win',
                'p1_points', 'p2_points'
            ];
            $values = [
                trim($_POST['p1-name']), trim($_POST['p2-name']),
                trim($_POST['p1-bid']), trim($_POST['p2-bid']),
                trim($_POST['p1-wins']), trim($_POST['p2-wins']),
                $p1Score, $p2Score
            ];

            $result = $db->updateById('games', $id, 'num', $columns, $values);
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
    <title>View Games</title>
</head>
<body class="bg-dark">

<?php
    if ($result){
        echo '<div class="container bg-success border border-success rounded max-width-350 mt-3">'
            .'<span class="text-center text-light">Successfully Updated!</span>'
            .'<a class=\'btn btn-primary m-3\' href=\'view.php\'>Back to View</a> '
            .'</div>';
    }

?>




<div class="container bg-light border border-success rounded mt-5 p-3">
    <h1 class="text-center">Update Game</h1>
    <h3 class="text-center"><?php echo "# $num on $date"?></h3>

    <form method="post" action="update.php?action=submit&id=<?php echo $num?>">
        <h5>Player 1</h5>
        <div class="form-row m-3">
            <div class="col">
                <h5 class="text-center">Name</h5>
                <input type="text" class="form-control" name="p1-name" value="<?php echo $data[2]?>">
            </div>
            <div class="col">
                <h5 class="text-center">Bid</h5>
                <input type="number" class="form-control" name="p1-bid" value="<?php echo $data[4]?>">
            </div>
            <div class="col">
                <h5 class="text-center">Wins</h5>
                <input type="number" class="form-control" name="p1-wins" value="<?php echo $data[6]?>">
            </div>
        </div>
        <h5>Player 2</h5>
        <div class="form-row m-3">
            <div class="col">
                <input type="text" class="form-control" name="p2-name" value="<?php echo $data[3]?>">
            </div>
            <div class="col">
                <input type="number" class="form-control" name="p2-bid" value="<?php echo $data[5]?>">
            </div>
            <div class="col">
                <input type="number" class="form-control" name="p2-wins" value="<?php echo $data[7]?>">
            </div>
        </div>

        <div class="max-width-350 m-auto">
            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </div>
    </form>

</div>

<footer class="text-white bg-dark p-3 text-center mt-auto">
    This site <span class="far fa-copyright"></span> 2020 by Barrington Venables
</footer>
</body>
</html>
