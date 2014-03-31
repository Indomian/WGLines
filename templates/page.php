<?php
/**
 * @var View $this
 * @var Engine $engine
 */
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
		<title>Lines</title>
		<link href="/css/style.css" type="text/css" rel="stylesheet"/>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="/css/bootstrap.min.css"/>
		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script src="/js/bootstrap.min.js"></script>

		<script type="text/javascript" src="/js/lines.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-sm-8">
					<?php $this->render('gameField',array('field'=>$engine->getField()));?>
				</div>
				<div class="col-sm-4">
					<div class="row">
						<h2 class="page-header">Счёт: <span id="score"><?php echo $engine->getScore()?></span></h2>
					</div>
					<div class="row">
						<h2 class="page-header">Ходы</h2>
						<div style="height:200px;overflow: auto;">
							<ul class="list-group" id="steps">
								<?php
								$arList=$engine->getStepsList();
								foreach($arList as $index=>$step):?>
									<li class="list-group-item" data-index="<?php echo $index?>"><?php echo $step?></li>
								<?php endforeach?>
							</ul>
						</div>
					</div>
					<div class="row">
						<h2 class="page-header">Действия</h2>
						<div class="btn-group">
							<button class="btn btn-danger" id="newGame">Новая игра</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>