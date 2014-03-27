<?php
/**
 * Class for rendering different templates
 */
class View {
	public $templateSourcePath='/templates/';
	private $arColors=array(
		0=>'#aaa',
		1=>'#f00',
		2=>'#0f0',
		3=>'#00f',
		4=>'#ff0',
		5=>'#fff',
		6=>'#f0f',
		7=>'#000',
	);

	public function __construct($templatePath=null) {
		if(!is_null($templatePath)) {
			$this->templateSourcePath=$templatePath;
		}
	}

	public function getHtmlColor($color) {
		return $this->arColors[$color];
	}

	public function render($template,$arData=array(),$bReturn=false) {
		if(file_exists(ROOT_DIR.$this->templateSourcePath.$template.'.php')) {
			extract($arData);
			ob_start();
			include(ROOT_DIR.$this->templateSourcePath.$template.'.php');
			$sResult=ob_get_clean();
			if($bReturn) {
				return $sResult;
			}
			echo $sResult;
		} else {
			throw new Exception('Template '.$template.' not found');
		}
	}
}