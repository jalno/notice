<?php
namespace packages\notice\Views\Note;
use \packages\notice\Views\Form;
use \packages\notice\Events\Views;
class Add extends Form{
	public function setViews(Views $views){
		$this->setData($views, 'views');
	}
	protected function getViews():Views{
		return $this->getData('views');
	}
	public function setUserTypes(array $types){
		$this->setData($types, 'types');
	}
	protected function getUserTypes():array{
		return $this->getData('types');
	}
}
