<?php
namespace packages\notice\events\views;
class inputNameException extends \Exception {
	private $input;
	public function __construct($input){
		$this->input = $input;
	}
	public function getController(){
		return $this->input;
	}
}
