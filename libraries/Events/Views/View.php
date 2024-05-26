<?php
namespace packages\notice\Events\Views;
use \packages\base\Event;
class View{
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
