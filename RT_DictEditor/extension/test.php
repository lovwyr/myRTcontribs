<?php
require_once('FirePHPCore/FirePHP.class.php');   

$firephp = FirePHP::getInstance(true);

$page = $_GET['$page'];
$item = $_GET['$item'];

$firephp->log($page, '$page');
$firephp->log($item, '$item');