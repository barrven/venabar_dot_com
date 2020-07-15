<div class="text-center text-white bg-success border border-success rounded p-3">
    <!-- logout button uses action param to tell this page to logout-->
    <a class="btn btn-primary float-right" href="?action=logout">Logout</a>
    <h1>
        <span class="fas fa-robot"></span>
        <?php echo 'Welcome, ' . @$username . '!'; ?>
        <span class="fas fa-space-shuttle"></span>
    </h1>

</div>