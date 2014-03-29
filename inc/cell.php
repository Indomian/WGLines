<?php
/**
 * Class used to store cell data
 */
class Cell implements Serializable{
	private $_field;
	private $_color;
	private $_futureColor;

	public function __construct(Field $field) {
		$this->_field=$field;
		$this->_color=0;
		$this->_futureColor=0;
	}

	public function setField($value) {
		$this->_field=$value;
	}

	public function setColor($color) {
		$this->_color=$color;
	}

	public function getColor() {
		return $this->_color;
	}

	public function isEmpty() {
		return $this->_color==0;
	}

	public function setFutureColor($color) {
		$this->_futureColor=$color;
	}

	public function getFutureColor() {
		return $this->_futureColor;
	}

	public function raiseCell() {
		if($this->_futureColor!=0) {
			$this->_color=$this->_futureColor;
			$this->_futureColor=0;
		}
	}

	public function serialize() {
		return $this->_color.':'.$this->_futureColor;
	}

	public function unserialize($value) {
		$value=explode(':',$value);
		$this->_color=$value[0];
		$this->_futureColor=$value[1];
	}
}