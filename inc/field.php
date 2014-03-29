<?php
require_once ROOT_DIR.'/inc/cell.php';
/**
 * Class used to store field data
 */
class Field implements Serializable {
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
		for($i=0;$i<$this->_size;$i++) {
			for($j=0;$j<$this->_size;$j++) {
				$this->_arField[$i][$j]=$this->createCell();
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
		for($i=0;$i<$this->_size;$i++) {
			for($j=0;$j<$this->_size;$j++) {
				$arResult['cells'][]=serialize($this->getCell($i,$j));
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
		for($i=0;$i<$this->_size;$i++) {
			for($j=0;$j<$this->_size;$j++) {
				$newCell=unserialize($arInput['cells'][$j*$this->_size+$i]);
				$newCell->setField($this);
				$this->_arField[$i][$j]=$newCell;
			}
		}
	}
}