<?php
/**
 * Class used to store cell data
 */
class Cell {
	private $_field;
	private $_color;
	private $_futureColor;

	public function __construct(Field $field) {
		$this->_field=$field;
		$this->_color=0;
		$this->_futureColor=0;
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
}