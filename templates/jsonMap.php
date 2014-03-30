<?php
/**
 * File renders gamefield in json format for js engine part
 * @var View $this
 * @var Field $field
 * @var Cell $cell
 */
$arResult=array();
foreach($field as $x=>$column) {
	foreach($column as $y=>$cell) {
		$arResult[$x][$y]=array(
			'class'=>'cell-content '.$this->getHtmlColor($cell->getColor()).' future-'.$this->getHtmlColor($cell->getFutureColor())
		);
	}
}
echo json_encode($arResult);