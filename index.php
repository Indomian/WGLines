<?php
/**
 * Main file for including everything
 */
ini_set('display_errors',1);
ini_set('error_reporting',E_ALL);
include_once 'config.php';
require_once ROOT_DIR.'/inc/render.php';
require_once ROOT_DIR.'/inc/field.php';

$obRender=new View();
$obField=new Field();

$obRender->render('page',array('field'=>$obField));