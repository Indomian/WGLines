<?php
require_once ROOT_DIR.'/inc/cell.php';
/**
 * Class used to store field data
 */
class Field implements Serializable, Iterator {
	const CELL_EMPTY=0;

	private $_size;
	private $_arField;

	public function __construct($size=9) {
		$this->_size=$size;
		$this->initField();
	}

	public function getSize() {
		return $this->_size;
	}

	public function initField() {
		for($x=0;$x<$this->_size;$x++) {
			for($y=0;$y<$this->_size;$y++) {
				$this->_arField[$x][$y]=$this->createCell();
			}
		}
	}

	/**
	 * @param $x
	 * @param $y
	 * @return Cell
	 * @throws OutOfBoundsException
	 */
	public function getCell($x,$y) {
		if($x>=0 && $x<$this->_size &&
			$y>=0 && $y<$this->_size) {
			return $this->_arField[$x][$y];
		}
		throw new OutOfBoundsException('Values out of size');
	}

	private function createCell() {
		return new Cell($this);
	}

	public function serialize() {
		$arResult=array(
			'size'=>$this->_size,
			'cells'=>array()
		);
		for($y=0;$y<$this->_size;$y++) {
			for($x=0;$x<$this->_size;$x++) {
				$arResult['cells'][]=serialize($this->getCell($x,$y));
			}
		}
		return json_encode($arResult);
	}

	public function unserialize($value) {
		$arInput=json_decode($value,true);
		if(!isset($arInput['size']))
			throw new Exception('Wrong input data');
		$this->_size=$arInput['size'];
		$this->_arField=array();
		for($x=0;$x<$this->_size;$x++) {
			for($y=0;$y<$this->_size;$y++) {
				$newCell=unserialize($arInput['cells'][$y*$this->_size+$x]);
				$newCell->setField($this);
				$this->_arField[$x][$y]=$newCell;
			}
		}
	}

	private $colIndex;

	/* Methods */
	public function current () {
		return $this->_arField[$this->colIndex];
	}

	public function key () {
		return $this->colIndex;
	}

	public function next () {
		$this->colIndex++;
	}

	public function rewind () {
		$this->colIndex=0;
	}

	public function valid () {
		return $this->colIndex<$this->_size;
	}
}