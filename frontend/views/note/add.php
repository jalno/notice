<?php
namespace themes\clipone\views\notice\note;
use \packages\base\json;
use \packages\notice\note;
use \packages\userpanel\date;
use \packages\base\view\error;
use \packages\base\translator;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \packages\userpanel\usertype;
use \packages\base\frontend\theme;
use \themes\clipone\views\formTrait;
use \packages\notice\views\note\add as addNote;
class add extends addNote{
	use viewTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle([
			translator::trans('notice'),
			translator::trans('notice.note.add')
		]);
		navigation::active("settings/notice");
		$this->addBodyClass('notice');
		$this->addBodyClass('add-note');
		$this->setFormData();
		$this->addAssets();
	}
	public function addAssets(){
		$this->addJSFile(theme::url('assets/plugins/ckeditor/ckeditor.js'));
	}
	private function setFormData(){
		if(!$this->getDataForm('create_at')){
			$this->setDataForm(date::format('Y/m/d H:i:s', date::time()), 'create_at');
		}
		if(!$this->getDataForm('expire_at')){
			$this->setDataForm(date::format('Y/m/d H:i:s', date::time() + 30 * 86400), 'expire_at');
		}
		if(!$this->getDataForm('user')){
			$this->setDataForm('all', 'user');
		}
		if(!$this->getDataForm('style')){
			$this->setDataForm(error::SUCCESS, 'style');
		}
		if(!$this->getDataForm('time[start]')){
			$this->setDataForm(0, 'time[start]');
		}
		if(!$this->getDataForm('time[end]')){
			$this->setDataForm(23, 'time[end]');
		}
		if(!$this->getDataForm('show-option')){
			$this->setDataForm('expire_at', 'show-option');
		}
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
			if($address = $this->getDataForm('address') and $address == $view->getView()){
				$views[] = [
					'title' => $address,
					'value' => $view->getView(),
					'data' => [
						'custom' => true
					]
				];
			}else{
				$views[] = [
					'title' => translator::trans('notice.note.view.name.'.$view->getName()),
					'value' => $view->getView()
				];
			}
			
		}
		return $views;
	}
	private function getUserTypeParents(usertype $type):string{
		$parnets = [];
		foreach($type->parents as $parent){
			$parnets[] = $parent->parent;
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
}
