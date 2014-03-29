<?php
/**
 * Class for rendering different templates
 */
class View {
	public $templateSourcePath='/templates/';
	private $arColors=array(
		0=>'empty-cell',
		1=>'red',
		2=>'green',
		3=>'blue',
		4=>'yellow',
		5=>'white',
		6=>'purple',
		7=>'black',
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