<?php
/**
 * Main file for including everything
 */
header('Cache-control: no-cache');
ini_set('display_errors',1);
ini_set('error_reporting',E_ALL);
include_once 'config.php';
require_once ROOT_DIR.'/inc/render.php';
require_once ROOT_DIR.'/inc/engine.php';

$obRender=new View();
$obEngine=new Engine();

$obRender->render('page',array('field'=>$obEngine->getField()));
$obEngine->save();