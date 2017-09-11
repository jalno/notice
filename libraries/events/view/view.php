<?php
namespace packages\notice\events\views;
use \packages\base\event;
class view{
	private $name = '';
	private $view = '';
	function __construct(string $view, string $name = null){
		$this->setView($view);
		if($name === null){
			$name = str_replace("\\", ".", $view);
		}
		$this->setName($name);
	}
	public function setName(string $name){
		$this->name = $name;
	}
	public function getName():string{
		return $this->name;
	}
	public function setView(string $view){
		$this->view = $view;
	}
	public function getView():string{
		return $this->view;
	}
}
