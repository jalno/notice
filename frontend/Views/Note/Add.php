<?php
namespace themes\clipone\Views\Notice\Note;
use \packages\base\Json;
use \packages\notice\Note;
use \packages\userpanel\Date;
use \packages\base\View\Error;
use \packages\base\Translator;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \packages\userpanel\UserType;
use \packages\base\Frontend\Theme;
use \themes\clipone\Views\FormTrait;
use \packages\notice\Views\Note\Add as AddNote;
class Add extends AddNote{
	use ViewTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle([
			Translator::trans('notice'),
			Translator::trans('notice.note.add')
		]);
		Navigation::active("settings/notice");
		$this->addBodyClass('notice');
		$this->addBodyClass('add-note');
		$this->setFormData();
		$this->addAssets();
	}
	public function addAssets(){
		$this->addJSFile(Theme::url('assets/plugins/ckeditor/ckeditor.js'));
	}
	private function setFormData(){
		if(!$this->getDataForm('create_at')){
			$this->setDataForm(Date::format('Y/m/d H:i:s', Date::time()), 'create_at');
		}
		if(!$this->getDataForm('expire_at')){
			$this->setDataForm(Date::format('Y/m/d H:i:s', Date::time() + 30 * 86400), 'expire_at');
		}
		if(!$this->getDataForm('user')){
			$this->setDataForm('all', 'user');
		}
		if(!$this->getDataForm('style')){
			$this->setDataForm(Error::SUCCESS, 'style');
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
				'title' => Translator::trans("notice.note.status.active"),
				'value' => Note::active
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
				'title' => Translator::trans("notice.note.type.".Note::alert),
				'value' => Note::alert
			],
			[
				'title' => Translator::trans("notice.note.type.".Note::modal),
				'value' => Note::modal
			]
		];
	}
	protected function getViewsForSelect():array{
		$views = [];
		foreach($this->getViews()->get() as $view){
			$translator = Translator::trans('notice.note.view.name.'.$view->getName());
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
	private function getUserTypeParents(UserType $type):string{
		$parnets = [];
		if($type->parents){
			foreach($type->parents as $parent){
				$parnets[] = $parent->parent;
			}
		}
		return Json\Encode($parnets);
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
