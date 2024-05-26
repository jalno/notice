<?php
namespace packages\notice\Views\Note;
use \packages\notice\Note;
use \packages\userpanel\Date;
use \packages\notice\Views\Form;
use \packages\notice\Events\Views;
class Edit extends Form{
	public function setNote(Note $note){
		$this->setData($note, 'note');
		$this->setDataForm($note->toArray());
		$this->setDataForm(Date::format('Y/m/d H:i:s', $note->create_at), 'create_at');
		$this->setDataForm(Date::format('Y/m/d H:i:s', $note->expire_at), 'expire_at');
		$this->setDataForm($note->start_time, 'time[start]');
		$this->setDataForm($note->end_time, 'time[end]');
		foreach($note->params as $param){
			$this->setDataForm($param->value, $param->name);
		}
		foreach($note->getUsers() as $user){
			$this->setDataForm($user['user'], "users[{$user['user']}]");
		}
		$this->setDataForm($note->getUserTypes(), "usertypes");
	}
	protected function getNote():Note{
		return $this->getData('note');
	}
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
