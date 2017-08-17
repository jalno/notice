<?php
namespace packages\notice\views\note;
use \packages\notice\views\form;
use \packages\notice\events\views;
class add extends form{
	public function setViews(views $views){
		$this->setData($views, 'views');
	}
	protected function getViews():views{
		return $this->getData('views');
	}
	public function setUserTypes(array $types){
		$this->setData($types, 'types');
	}
	protected function getUserTypes():array{
		return $this->getData('types');
	}
}
