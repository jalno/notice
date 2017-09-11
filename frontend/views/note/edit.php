<?php
namespace themes\clipone\views\notice\note;
use \packages\base\json;
use \packages\notice\note;
use \packages\userpanel\user;
use \packages\userpanel\date;
use \packages\base\view\error;
use \packages\base\translator;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \packages\userpanel\usertype;
use \packages\base\frontend\theme;
use \themes\clipone\views\formTrait;
use \packages\notice\views\note\edit as noteEdit;
class edit extends noteEdit{
	use viewTrait, formTrait;
	protected $note;
	function __beforeLoad(){
		$this->note = $this->getNote();
		$this->setTitle([
			translator::trans('notice'),
			translator::trans('notice.note.edit')
		]);
		navigation::active("settings/notice");
		$this->addBodyClass('notice');
		$this->addBodyClass('edit-note');
		$this->addAssets();
	}
	public function addAssets(){
		$this->addJSFile(theme::url('assets/plugins/ckeditor/ckeditor.js'));
	}
	protected function getStatusForSelect():array{
		return [
			[
				'title' => translator::trans("notice.note.status.active"),
				'value' => note::active
			],
			[
				'title' => translator::trans("notice.note.status.deactive"),
				'value' => note::deactive
			]
		];
	}
	protected function getTypeForSelect():array{
		return [
			[
				'title' => translator::trans("notice.note.type.".note::alert),
				'value' => note::alert
			],
			[
				'title' => translator::trans("notice.note.type.".note::modal),
				'value' => note::modal
			]
		];
	}
	protected function getViewsForSelect():array{
		$views = [];
		foreach($this->getViews()->get() as $view){
			$translator = translator::trans('notice.note.view.name.'.$view->getName());
			$views[] = [
				'title' => $translator ? $translator : $view->getName(),
				'value' => $view->getView(),
				'data' => [
					'custom' => ($address = $this->getDataForm('address') and $address == $view->getView())
				]
			];
		}
		return $views;
	}
	private function getUserTypeParents(usertype $type):string{
		$parnets = [];
		if($type->parents){
			foreach($type->parents as $parent){
				$parnets[] = $parent->parent;
			}
		}
		return json\encode($parnets);
	}
	protected function getUserTypesForSelect():array{
		$types = [];
		foreach($this->getUserTypes() as $type){
			$types[] = [
				'label' => $type->title,
				'value' => $type->id,
				'data' => [
					'parent' => $this->getUserTypeParents($type)
				]
			];
		}
		return $types;
	}
	protected function getUsersForCheck():array{
		$users = [];
		foreach($this->note->getUsers() as $user){
			$users[] = user::byId($user['user']);
		}
		return $users;
	}
}
