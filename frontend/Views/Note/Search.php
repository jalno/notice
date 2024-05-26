<?php
namespace themes\clipone\Views\Notice\Note;
use \packages\base\Packages;
use \packages\base\View\Error;
use \packages\base\Translator;
use \packages\userpanel;
use \packages\notice\Note;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Views\ListTrait;
use \themes\clipone\Views\FormTrait;
use \themes\clipone\Navigation\MenuItem;
use \packages\notice\Views\Note\Search as NoticeList;
class Search extends NoticeList{
	use ViewTrait, ListTrait, FormTrait;
	function __beforeLoad(){
		$this->setTitle([
			Translator::trans('notice')
		]);
		$this->setButtons();
		Navigation::active("notice");
		if(empty($this->getNoteLists())){
			$this->addNotFoundError();
		}
	}
	private function addNotFoundError(){
		$error = new Error();
		$error->setType(Error::NOTICE);
		$error->setCode('notice.note.notfound');
		if($this->canAdd){
			$error->setData([
				[
					'type' => 'btn-success',
					'txt' => Translator::trans('notice.note.add'),
					'link' => userpanel\url('settings/notice/notes/add')
				]
			], 'btns');
		}
		$this->addError($error);
	}
	public function setButtons(){
		$this->setButton('edit', $this->canEdit, [
			'title' => Translator::trans('notice.note.edit'),
			'icon' => 'fa fa-edit',
			'classes' => ['btn', 'btn-xs', 'btn-teal']
		]);
		$this->setButton('delete', $this->canDel, [
			'title' => Translator::trans('notice.note.delete'),
			'icon' => 'fa fa-times',
			'classes' => ['btn', 'btn-xs', 'btn-bricky']
		]);
	}
	public static function onSourceLoad(){
		parent::onSourceLoad();
		if(parent::$navigation){
			if($settings = Navigation::getByName("settings")){
				$item = new MenuItem("notice");
				$item->setTitle(Translator::trans('notice'));
				$item->setURL(userpanel\url('settings/notice/notes'));
				$item->setIcon('fa fa-bell');
				$settings->addItem($item);
			}
		}
	}
	public function getComparisonsForSelect(){
		return [
			[
				'title' => Translator::trans('search.comparison.contains'),
				'value' => 'contains'
			],
			[
				'title' => Translator::trans('search.comparison.equals'),
				'value' => 'equals'
			],
			[
				'title' => Translator::trans('search.comparison.startswith'),
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
			if($name = Translator::trans('notice.note.view.name.'.$view->getName())){
				return $name;
			}
		}
		return "<a class=\"pull-left\" href=\"{$note->view}\" target=\"_blank\">{$note->view}</a>";
	}
	protected function getStatusForSelect():array{
		return [
			[
				'title' => Translator::trans("notice.search.choose"),
				'value' => ''
			],
			[
				'title' => Translator::trans("notice.note.status.active"),
				'value' => Note::active
			],
			[
				'title' => Translator::trans("notice.note.status.deactive"),
				'value' => Note::deactive
			]
		];
	}
	protected function getTypeForSelect():array{
		return [
			[
				'title' => Translator::trans("notice.search.choose"),
				'value' => ''
			],
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
}
