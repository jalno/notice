<?php
namespace packages\notice\views\note;
use \packages\notice\note;
use \packages\notice\views\form;
class delete extends form{
	public function setNote(note $note){
		$this->setData($note, 'note');
	}
	protected function getNote():note{
		return $this->getData('note');
	}
}
