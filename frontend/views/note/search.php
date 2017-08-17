<?php
namespace themes\clipone\views\notice\note;
use \packages\base\packages;
use \packages\base\view\error;
use \packages\base\translator;
use \packages\userpanel;
use \packages\notice\note;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\views\listTrait;
use \themes\clipone\views\formTrait;
use \themes\clipone\navigation\menuItem;
use \packages\notice\views\note\search as noticeList;
class search extends noticeList{
	use viewTrait, listTrait, formTrait;
	function __beforeLoad(){
		$this->setTitle([
			translator::trans('notice')
		]);
		$this->setButtons();
		navigation::active("notice");
		if(empty($this->getNoteLists())){
			$this->addNotFoundError();
		}
	}
	private function addNotFoundError(){
		$error = new error();
		$error->setType(error::NOTICE);
		$error->setCode('notice.note.notfound');
		if($this->canAdd){
			$error->setData([
				[
					'type' => 'btn-success',
					'txt' => translator::trans('notice.note.add'),
					'link' => userpanel\url('settings/notice/notes/add')
				]
			], 'btns');
		}
		$this->addError($error);
	}
	public function setButtons(){
		$this->setButton('edit', $this->canEdit, [
			'title' => translator::trans('notice.note.edit'),
			'icon' => 'fa fa-edit',
			'classes' => ['btn', 'btn-xs', 'btn-teal']
		]);
		$this->setButton('delete', $this->canDel, [
			'title' => translator::trans('notice.note.delete'),
			'icon' => 'fa fa-times',
			'classes' => ['btn', 'btn-xs', 'btn-bricky']
		]);
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			if($settings = navigation::getByName("settings")){
				$item = new menuItem("notice");
				$item->setTitle(translator::trans('notice'));
				$item->setURL(userpanel\url('settings/notice/notes'));
				$item->setIcon('fa fa-bell');
				$settings->addItem($item);
			}
		}
	}
	public function getComparisonsForSelect(){
		return [
			[
				'title' => translator::trans('search.comparison.contains'),
				'value' => 'contains'
			],
			[
				'title' => translator::trans('search.comparison.equals'),
				'value' => 'equals'
			],
			[
				'title' => translator::trans('search.comparison.startswith'),
				'value' => 'startswith'
			]
		];
	}
	protected function getNoteViewName(note $note):string{
		static $views;
		if(!$views){
			$views = $this->getViews();
		}
		if($view = $views->getByView($note->view)){
			if($name = translator::trans('notice.note.view.name.'.$view->getName())){
				return $name;
			}
		}
		return "<a class=\"pull-left\" href=\"{$note->view}\" target=\"_blank\">{$note->view}</a>";
	}
	protected function getStatusForSelect():array{
		return [
			[
				'title' => translator::trans("notice.search.choose"),
				'value' => ''
			],
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
				'title' => translator::trans("notice.search.choose"),
				'value' => ''
			],
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
}
