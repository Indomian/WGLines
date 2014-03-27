<?php
/**
 * @var View $this
 * @var Field $field
 */
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<title>Lines</title>
		<link href="/css/style.css" type="text/css" rel="stylesheet"/>
		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script type="text/javascript" src="/js/lines.js"></script>
	</head>
	<body>
		<?php $this->render('gameField',array('field'=>$field));?>
	</body>
</html>