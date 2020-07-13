<?php include 'header.php' ?>
<!-- template from: https://www.w3schools.com/bootstrap4/bootstrap_forms.asp  -->
<div class="container bg-light max-width-350 mt-5 p-5 border border-primary rounded">
    <h2 class="text-center pb-2">
        <span class="fas fa-user-astronaut"></span>
        &nbsp; Login &nbsp;
        <span class="fas fa-satellite-dish"></span>
    </h2>
    <p class="text-center text-danger">
        <?php
        //to check if login_test.php has redirected back to login form due to incorrect password
            if(@$_SESSION['login_attempted']){
                echo 'Username or password not found<br>';
                if ($_SESSION['db_error_msg']){
                    echo 'Could not connect to database';
                }
            }
        ?>
    </p>
    <form action="<?php echo htmlspecialchars('../.utils/login_check.php') ?>" class="was-validated" method="post">
        <!--username input-->
        <div class="form-group">
            <input type="text" class="form-control" id="username"
                   placeholder="Enter username" name="username" required>
        </div>
        <!--password input-->
        <div class="form-group">
            <input type="password" class="form-control" id="pwd"
                   placeholder="Enter password" name="pwd" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Submit</button>
    </form>
</div>
<?php include 'footer.php'?>