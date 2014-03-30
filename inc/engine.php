<?php
require_once ROOT_DIR.'/inc/field.php';
require_once ROOT_DIR.'/inc/render.php';

class Engine {
	const ADD_CIRCLES_COUNT=3;
	const BLANK_CELL=-2;
	const BUSY_CELL=-1;
	private $arPoints=array(
		5=>10,
		6=>12,
		7=>18,
		8=>28,
		9=>42
	);
	/**
	 * @var Field
	 */
	private $_field;
	private $_score;
	private $_move;
	private $obRender;
	/**
	 * @var Array
	 */
	private $arEmptyCells;
	private $arNewCircles;
	private $_gameOver=false;

	public function __construct() {
		session_start();
		$this->obRender=new View();
		if(!$this->load()) {
			$this->init();
		}
	}

	public function getField() {
		return $this->_field;
	}

	public function load() {
		if(!isset($_SESSION['steps'])) {
			return false;
		}
		$arLastStep=$_SESSION['steps'][0];
		if(!is_array($arLastStep)) {
			return false;
		}
		if(!isset($arLastStep['field'])) {
			return false;
		}
		$this->_field=unserialize($arLastStep['field']);
		$this->_score=$arLastStep['score'];
		return true;
	}

	public function save() {
		if($this->_gameOver) {
			unset($_SESSION['steps']);
		} else {
			if(!isset($_SESSION['steps'])) {
				$_SESSION['steps']=array();
			}
			if(!is_array($_SESSION['steps'])) {
				$_SESSION['steps']=array();
			}
			array_unshift($_SESSION['steps'],array(
				'field'=>serialize($this->_field),
				'score'=>$this->_score,
				'move'=>$this->_move
			));
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
			$to->setFutureColor(0);
			$this->_move=$fromX.':'.$fromY.'->'.$toX.':'.$toY;
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
	 * @return boolean
	 */
	public function findPath($fromX,$fromY,$toX,$toY) {
		/**
		 * @var Cell $cell
		 */
		$arMap=array();
		foreach($this->_field as $x=>$col) {
			foreach($col as $y=>$cell) {
				if($cell->isEmpty()) {
					$arMap[$x][$y]=self::BLANK_CELL;
				} else {
					$arMap[$x][$y]=self::BUSY_CELL;
				}
			}
		}

		$arDelta=array(
			array(1,0),
			array(0,1),
			array(-1,0),
			array(0,-1)
		);
		$size=$this->_field->getSize();
		$arPath=array();
		$iDistance=0;
		$arMap[$fromX][$fromY]=0;
		do {
			$stop = true;
			for ( $y = 0; $y < $size; $y++ )
				for ( $x = 0; $x < $size; $x++ )
					if ( $arMap[$x][$y] == $iDistance ) {
						foreach($arDelta as $arCoord) {
							$nx=$x+$arCoord[0];
							$ny=$y+$arCoord[1];
							if($nx>-1 && $nx<$size && $ny>-1 && $ny<$size) {
								if ($arMap[$nx][$ny] == self::BLANK_CELL) {
									$stop=false;
									$arMap[$nx][$ny]=$iDistance+1;
								}
							}
						}
					}
			$iDistance++;
		} while (!$stop && $arMap[$toX][$toY]==self::BLANK_CELL);

		if ($arMap[$toX][$toY]==self::BLANK_CELL) return false;  // путь не найден

		// восстановление пути
		$length=$arMap[$toX][$toY];
		$x=$toX;
		$y=$toY;
		$iDistance=$length;
		while($iDistance>0) {
			$arPath[$iDistance]=array($x,$y);
			$iDistance--;
			foreach($arDelta as $arCoord) {
				$nx=$x+$arCoord[0];
				$ny=$y+$arCoord[1];
				if($nx>-1 && $nx<$size && $ny>-1 && $ny<$size) {
					if($arMap[$nx][$ny]==$iDistance) {
						$x=$nx;
						$y=$ny;           // переходим в ячейку, которая на 1 ближе к старту
						break;
					}
				}
			}
		}
		$arPath[0]=array($fromX,$fromY);
		return true;
	}

	private function step() {
		if(!$this->findLine()) {
			$this->addCircles();
		}
		$this->nextCircles();
	}

	private function init() {
		$this->_field=new Field(9);
		$this->_score=0;
		$this->fillEmptyList();
		$this->nextCircles();
		$this->addCircles();
		$this->nextCircles();
	}

	private function nextCircles($count=self::ADD_CIRCLES_COUNT) {
		$count=min($count,count($this->arEmptyCells));
		if($count==0) {
			$this->_gameOver=true;
			return;
		}
		$arPositions=array_rand($this->arEmptyCells,$count);
		if($count==1) {
			$arPositions=array($arPositions);
		}
		foreach($arPositions as $index) {
			$this->_field->getCell($this->arEmptyCells[$index][0],$this->arEmptyCells[$index][1])->setFutureColor(rand(1,7));
		}
		$this->fillEmptyList();
	}

	private function addCircles() {
		if(count($this->arNewCircles)<self::ADD_CIRCLES_COUNT) {
			$this->nextCircles();
		}
		foreach($this->arNewCircles as $arCoord) {
			$this->_field->getCell($arCoord[0],$arCoord[1])->raiseCell();
		}
		$this->fillEmptyList();
	}

	private function findLine() {
		$bResult=false;
		for($y=0;$y<$this->_field->getSize();$y++) {
			$arLineLength=array();
			$currentColor=-1;
			for($x=0;$x<$this->_field->getSize();$x++) {
				$cellColor=$this->getCell($x,$y)->getColor();
				if($cellColor!=$currentColor) {
					if(count($arLineLength)>4 && $currentColor>0) {
						$this->_score+=$this->arPoints[count($arLineLength)];
						foreach($arLineLength as $coord) {
							$this->getCell($coord,$y)->setColor(0);
						}
						$bResult=true;
						break 2;
					} else {
						$arLineLength=array();
						$currentColor=$cellColor;
					}
				} else {
					$arLineLength[]=$x;
				}
			}
		}
		return $bResult;
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

	public function process($action) {
		$view='page';
		if($action=='restart') {
			$this->init();
			$this->_score=0;
			$this->_move='New game';
			$view='json';
			$arResult=array(
				'score'=>$this->_score,
				'map'=>json_decode($this->obRender->render('jsonMap',array('field'=>$this->_field),true),true),
				'move'=>$this->_move,
				'callback'=>array(
					'clearMoves'
				)
			);
			$arData=array(
				'json'=>$arResult
			);
		} elseif($action=='step') {
			$view='json';
			try {
				if(!isset($_GET['data'])) {
					throw new Exception('No required data');
				}
				$arRequest=$_GET['data'];
				$this->moveCell($arRequest['from']['x'],$arRequest['from']['y'],
									$arRequest['to']['x'],$arRequest['to']['y']);
				$this->fillEmptyList();
				$this->step();
				$arData=array(
					'json'=>array(
						'score'=>$this->_score,
						'map'=>json_decode($this->obRender->render('jsonMap',array('field'=>$this->_field),true),true),
						'move'=>$this->_move,
					)
				);
			} catch (UserInputException $e) {
				$arData=array(
					'json'=>array(
						'error'=>$e->getMessage(),
						'map'=>json_decode($this->obRender->render('jsonMap',array('field'=>$this->_field),true),true),
					)
				);
			} catch (Exception $e) {
				$arData=array(
					'json'=>array(
						'error'=>$e->getMessage()
					)
				);
			}
		} else {
			$arData=array('engine'=>$this);
		}
		$this->save();
		$this->obRender->render($view,$arData);
	}

	public function getScore() {
		return $this->_score;
	}
}

class UserInputException extends Exception {}