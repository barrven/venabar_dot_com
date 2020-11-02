<?php
require 'Database.php';
$connected = false;
$db = new Database();
$db->setQuery('select num, date, p1_name, p1_bid, p1_points, p2_name, p2_bid, p2_points from games');
if (!$db->getError()){
    $connected = true;
    $data = $db->getRecords();
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
<div class="container bg-light border border-success rounded mt-5 p-3">


    <table class='table table-striped' id=''>
        <thead class='thead-dark'>
        <tr>
            <th scope='col'>Date</th>
            <th scope='col'>Name</th>
            <th scope='col'>Bid</th>
            <th scope='col'>Points</th>
            <th scope='col'>Name</th>
            <th scope='col'>Bid</th>
            <th scope='col'>Points</th>
            <th style="width:  8%" scope='col'></th>
            <th style="width:  8%" scope='col'></th>
        </tr>
        </thead>
        <tbody class='bg-light'>
        <?php
            if ($connected){
                $p1_sum = 0;
                $p2_sum = 0;
                foreach ($data as $line){
                    echo '<tr>';
                    echo "<td>$line[1]</td>" //date
                        ."<td>$line[2]</td>" //p1 name
                        ."<td>$line[3]</td>" //p1 bid
                        ."<td>$line[4]</td>" //p1 points
                        ."<td>$line[5]</td>" //p2 name
                        ."<td>$line[6]</td>" //p2 bid
                        ."<td>$line[7]</td>" //p2 points
                        ."<td ><a class='btn btn-success' href='update.php?id=$line[0]'>Update</a></td>"
                        ."<td><a class='btn btn-danger' href='delete.php?id=$line[0]'>Delete</a></td>";

                    $p1_sum += intval($line[4]);
                    $p2_sum += intval($line[7]);
                }

                echo "<tr class='bg-dark text-light'>"
                    ."<td colspan='3' class='text-right'>Player 1 Total Score</td>"
                    ."<td>$p1_sum</td>"
                    ."<td colspan='2' class='text-right'>Player 2 Total Score</td>"
                    ."<td>$p2_sum</td>"
                    ."<td></td>"
                    ."<td></td>"
                    ."</tr>";
            }
            else{
                echo "<p class='text-danger text-center'>Could not connect to database</p>";
                $msg = $db->getErrorMessage();
                echo "<p class='text-danger text-center'>$msg</p>";
            }
        ?>
        </tbody>
    </table>

    <div class="max-width-350 m-auto">
        <button type="button" class="btn btn-primary btn-block" onclick="document.location.href='create.php'">Add New Game</button>
    </div>

</div>

<footer class="text-white bg-dark p-3 text-center mt-auto">
    This site <span class="far fa-copyright"></span> 2020 by Barrington Venables
</footer>
</body>
</html>
