<?php
namespace packages\notice\views\note;
use \packages\notice\note;
use \packages\userpanel\date;
use \packages\notice\views\form;
use \packages\notice\events\views;
class edit extends form{
	public function setNote(note $note){
		$this->setData($note, 'note');
		$this->setDataForm($note->toArray());
		$this->setDataForm(date::format('Y/m/d H:i:s', $note->create_at), 'create_at');
		$this->setDataForm(date::format('Y/m/d H:i:s', $note->expire_at), 'expire_at');
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
	protected function getNote():note{
		return $this->getData('note');
	}
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
