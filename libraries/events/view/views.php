<?php
namespace packages\notice\events;
use \packages\base\event;
use \packages\notice\events\views\view;
class views extends event{
	private $views = [];
	public function addView(view $view){
		$this->views[$view->getName()] = $view;
	}
	public function getViewNames():array{
		return array_keys($this->views);
	}
	public function getByName(string $name){
		return (isset($this->views[$name]) ? $this->views[$name] : null);
	}
	public function getByView(string $view){
		foreach($this->views as $obj){
			if($obj->getView() == $view){
				return $obj;
			}
		}
		return null;
	}
	public function get():array{
		return $this->views;
	}
}
