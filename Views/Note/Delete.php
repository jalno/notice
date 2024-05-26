<?php
namespace packages\notice\Views\Note;
use \packages\notice\Note;
use \packages\notice\Views\Form;
class Delete extends Form{
	public function setNote(Note $note){
		$this->setData($note, 'note');
	}
	protected function getNote():Note{
		return $this->getData('note');
	}
}
