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
		$arLastStep=$_SESSION['steps'][count($_SESSION['steps'])-1];
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
		}
		if(!isset($_SESSION['steps'])) {
			$_SESSION['steps']=array();
		}
		if(!is_array($_SESSION['steps'])) {
			$_SESSION['steps']=array();
		}
		array_push($_SESSION['steps'],array(
			'field'=>serialize($this->_field),
			'score'=>$this->_score,
			'move'=>$this->_move
		));
	}

	public function getCell($x,$y) {
		return $this->_field->getCell($x,$y);
	}

	/**
	 * Common function to process circle movement
	 * @param $fromX
	 * @param $fromY
	 * @param $toX
	 * @param $toY
	 * @throws UserInputException
	 */
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
	 * Method search for path between two points. Uses "wave" search
	 * algorithm. Variation of wide search algorithm.
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
		//Init search cell map in easy 2 dimensional array
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
		//Process "wave" through all map from start point
		//and fill all map with distance from start point
		//if it reaches destination point or all map field, algorithm stops
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

		//Check if final point unreachable
		if ($arMap[$toX][$toY]==self::BLANK_CELL) return false;

		return true;
	}

	private function step() {
		if(!$this->findLine()) {
			$this->addCircles();
			$this->findLine();
			$this->nextCircles();
		}
	}

	private function init() {
		$this->_field=new Field(9);
		$this->_score=0;
		$this->fillEmptyList();
		$this->nextCircles();
		$this->addCircles();
		$this->nextCircles();
		$this->save();
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

	/**
	 * Method searches for straight line of circles
	 * at least 5 in line. Just fills coord stack and
	 * checks if color changes. Use $isY to search lines
	 * vertically;
	 * @param bool $isY
	 * @return array
	 */
	private function findStraightLines($isY=false) {
		$arFilledLines=array();
		for($y=0;$y<$this->_field->getSize();$y++) {
			$arLineLength=array();
			$currentColor=-1;
			for($x=0;$x<$this->_field->getSize();$x++) {
				if($isY) {
					$cellColor=$this->getCell($y,$x)->getColor();
				} else {
					$cellColor=$this->getCell($x,$y)->getColor();
				}
				if($cellColor!=$currentColor) {
					if(count($arLineLength)>4 && $currentColor>0) {
						break;
					} else {
						$arLineLength=array();
					}
				}
				if($isY) {
					$arLineLength[]=array($y,$x);
				} else {
					$arLineLength[]=array($x,$y);
				}
				$currentColor=$cellColor;
			}
			if(count($arLineLength)>4 && $currentColor>0) {
				$arFilledLines[]=$arLineLength;
			}
		}
		return $arFilledLines;
	}

	/**
	 * Method search for diagonal lines in predefined direction. Checks main
	 * diagonal and upper right and bottom left trapezoids if $d=1, checks secondary
	 * diagonal and upper left and bottom right trapezoids if $d=-1
	 * Inner algorithm same as hor/ver lines search - check if color changes and fill stack
	 * @param $maxLine
	 * @param $deltaX
	 * @param $deltaY
	 * @param array &$arResult - saves result to provided array
	 * @param int $d
	 */
	private function _processDiagonalSector($maxLine,$deltaX,$deltaY,&$arResult,$d=1) {
		$arLineLength=array();
		$currentColor=-1;
		for($k=0;$k<$maxLine;$k++) {
			$cellColor=$this->getCell($k*$d+$deltaX,$k+$deltaY)->getColor();
			if($cellColor!=$currentColor) {
				if(count($arLineLength)>4 && $currentColor>0) {
					break;
				} else {
					$arLineLength=array();
				}
			}
			$arLineLength[]=array($k*$d+$deltaX,$k+$deltaY);
			$currentColor=$cellColor;
		}
		if(count($arLineLength)>4 && $currentColor>0) {
			$arResult[]=$arLineLength;
		}
	}

	/**
	 * Method searches for diagonal lines
	 * @return array
	 */
	private function findDiagonalLines() {
		$size=$this->_field->getSize();
		$max=$size-4;
		$arFilledLines=array();
		for($x=0;$x<$max;$x++) {
			$maxLine=$size-$x;
			$this->_processDiagonalSector($maxLine,$x,0,$arFilledLines);
			$this->_processDiagonalSector($maxLine,0,$x,$arFilledLines);
			$this->_processDiagonalSector($maxLine,$size-1,$x,$arFilledLines,-1);
			$this->_processDiagonalSector($maxLine,$size-1-$x,0,$arFilledLines,-1);
		}
		return $arFilledLines;
	}

	/**
	 * Method performs searching of all circles in lines
	 * @return bool
	 */
	private function findLine() {
		$arFilledLines=array();
		$bResult=false;
		//Find all horizontal lines
		$arFilledLines=array_merge($arFilledLines,$this->findStraightLines());
		//Find all vertical lines
		$arFilledLines=array_merge($arFilledLines,$this->findStraightLines(true));
		//Find all diagonal lines
		$arFilledLines=array_merge($arFilledLines,$this->findDiagonalLines());
		//Clear all find lines and add points to score
		if(count($arFilledLines)>0) {
			foreach($arFilledLines as $arLine) {
				$this->_score+=$this->arPoints[count($arLine)];
				foreach($arLine as $coord) {
					$this->getCell($coord[0],$coord[1])->setColor(0);
				}
			}
			$bResult=true;
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

	/**
	 * Main method to process game functions
	 * @param $action
	 */
	public function process($action) {
		$view='page';
		if($action=='restart') {
			$this->init();
			$this->_score=0;
			$this->_move='New game';
			$this->_gameOver=true;
			$this->save();
			$view='json';
			$arResult=array(
				'score'=>$this->_score,
				'map'=>json_decode($this->obRender->render('jsonMap',array('field'=>$this->_field),true),true),
				'move'=>$this->_move,
				'moveIndex'=>0,
				'callbacks'=>array(
					'clearMoves'
				)
			);
			$arData=array(
				'json'=>$arResult
			);
		} elseif($action=='reset') {
			$view='json';
			try {
				if(!isset($_GET['data'])) {
					throw new Exception('No required data');
				}
				$index=$_GET['data'];
				$count=count($_SESSION['steps']);
				if($index>=$count) {
					throw new Exception('Wrong back step');
				}
				$_SESSION['steps']=array_slice($_SESSION['steps'],0,$index+1);
				$this->load();
				$arData=array(
					'json'=>array(
						'score'=>$this->_score,
						'map'=>json_decode($this->obRender->render('jsonMap',array('field'=>$this->_field),true),true),
						'callbacks'=>array(
							'resetSteps'
						)
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
				$this->save();
				$arData=array(
					'json'=>array(
						'score'=>$this->_score,
						'map'=>json_decode($this->obRender->render('jsonMap',array('field'=>$this->_field),true),true),
						'move'=>$this->_move,
						'moveIndex'=>count($_SESSION['steps'])-1
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
		$this->obRender->render($view,$arData);
	}

	public function getScore() {
		return $this->_score;
	}

	public function getStepsList() {
		$arResult=array();
		if(!isset($_SESSION['steps'])) {
			return $arResult;
		}
		foreach($_SESSION['steps'] as $step) {
			array_push($arResult,$step['move']);
		}
		return $arResult;
	}
}

class UserInputException extends Exception {}