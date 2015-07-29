<?php
chdir(dirname(__FILE__));

if(!file_exists('./vendor/autoload.php')){
  die("You need to run <em>php composer.phar update</em> in the root directory.");
}

// Load Auto loaded things
require_once("./vendor/autoload.php");
