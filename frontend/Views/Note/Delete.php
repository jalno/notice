<?php
namespace themes\clipone\Views\Notice\Note;
use \packages\base\Translator;
use \packages\userpanel;
use \themes\clipone\ViewTrait;
use \themes\clipone\Navigation;
use \themes\clipone\Views\FormTrait;
use \packages\notice\Views\Note\Delete as NoticeDelete;
class Delete extends NoticeDelete{
	use ViewTrait, FormTrait;
	protected $note;
	function __beforeLoad(){
		$this->note = $this->getNote();
		$this->setTitle([
			Translator::trans('notice'),
			Translator::trans('notice.note.delete')
		]);
		Navigation::active("settings/notice");
		$this->addBodyClass('notice');
	}
}
