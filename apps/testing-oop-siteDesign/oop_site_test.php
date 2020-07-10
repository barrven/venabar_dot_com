<?php
//source: http://www.hackingwithphp.com/6/19/1/a-basic-oop-site
//require '../.config.php'; // get the functions and classes library
//if using classes in separate files can use spl_autoload_register() function to include them
//https://www.php.net/manual/en/language.oop5.autoload.php
include 'Page.php';
include 'Site.php';

$site = new Site();
$site->addHeader("header.php");
$site->addHeader('title.php');
$site->addHeader('style.php');
$site->addHeader('close_header.php');
$site->addFooter("footer.php");

$page = new Page('OOP Test');
$site->setPage($page);

$content = <<<EOT
    <div class="container border border-primary mt-3 p-2 shadow-lg">
        <h1 class="text-center">Hello!</h1>
        <p>Welcome to my personal web site!</p>
    </div>

EOT;

$page->setContent($content);
$site->render();