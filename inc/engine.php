<?php
require_once ROOT_DIR.'/inc/field.php';

class Engine {
	const ADD_CIRCLES_COUNT=3;
	/**
	 * @var Field
	 */
	private $_field;
	/**
	 * @var Array
	 */
	private $arEmptyCells;
	private $arNewCircles;
	private $_gameOver=false;

	public function __construct() {
		session_start();
		if(!$this->load()) {
			$this->init();
		}
		$this->fillEmptyList();
		$this->step();
	}

	public function getField() {
		return $this->_field;
	}

	public function load() {
		if(!isset($_SESSION['steps'])) {
			return false;
		}
		$this->_field=unserialize($_SESSION['steps']);
		return true;
	}

	public function save() {
		if($this->_gameOver) {
			unset($_SESSION['steps']);
		} else {
			$_SESSION['steps']=serialize($this->_field);
		}
	}

	public function getCell($x,$y) {
		return $this->_field->getCell($x,$y);
	}

	public function moveCell($fromX,$fromY,$toX,$toY) {
		$from=$this->getCell($fromX,$fromY);
		$to=$this->getCell($toX,$toY);
		if($from->isEmpty()) {
			throw new UserInputException('No circle in from point');
		}
		if(!$to->isEmpty()) {
			throw new UserInputException('Destination point busy');
		}
		if($this->findPath($fromX,$fromY,$toX,$toY)) {
			$to->setColor($from->getColor());
			$from->setColor(0);
		} else {
			throw new UserInputException('Destination point unreachable');
		}
	}

	/**
	 * Method search for path between two points
	 * @param $fromX
	 * @param $fromY
	 * @param $toX
	 * @param $toY
	 */
	public function findPath($fromX,$fromY,$toX,$toY) {

	}

	private function step() {
		if(!$this->findLine()) {
			$this->addCircles();
		}
		$this->nextCircles();
	}

	private function init() {
		$this->_field=new Field(9);
		$this->addCircles();
	}

	private function nextCircles($count=self::ADD_CIRCLES_COUNT) {
		$count=min($count,count($this->arEmptyCells));
		if($count==0) {
			$this->_gameOver=true;
			return;
		}
		$arPositions=array_rand($this->arEmptyCells,$count);
		foreach($arPositions as $index) {
			$this->_field->getCell($this->arEmptyCells[$index][0],$this->arEmptyCells[$index][1])->setFutureColor(rand(1,7));
		}
	}

	private function addCircles() {
		foreach($this->arNewCircles as $arCoord) {
			$this->_field->getCell($arCoord[0],$arCoord[1])->raiseCell();
		}
	}

	private function findLine() {
		return false;
	}

	private function fillEmptyList() {
		$this->arEmptyCells=array();
		$this->arNewCircles=array();
		for($i=0;$i<$this->_field->getSize();$i++) {
			for($j=0;$j<$this->_field->getSize();$j++) {
				$cell=$this->_field->getCell($i,$j);
				if($cell->isEmpty()) {
					$this->arEmptyCells[]=array($i,$j);
				}
				if($cell->getFutureColor()!=0) {
					$this->arNewCircles[]=array($i,$j);
				}
			}
		}
	}
}

class UserInputException extends Exception {}