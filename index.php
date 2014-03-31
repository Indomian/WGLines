<?php
/**
 * Main file for including everything
 */
define('ROOT_DIR',dirname(__FILE__));
header('Cache-control: no-cache');
ini_set('display_errors',1);
ini_set('error_reporting',E_ALL);
require_once ROOT_DIR.'/inc/engine.php';

$obEngine=new Engine();

$action=null;
if(isset($_GET['action'])) {
	$action=$_GET['action'];
}
$obEngine->process($action);