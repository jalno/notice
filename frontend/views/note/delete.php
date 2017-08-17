<?php
namespace themes\clipone\views\notice\note;
use \packages\base\translator;
use \packages\userpanel;
use \themes\clipone\viewTrait;
use \themes\clipone\navigation;
use \themes\clipone\views\formTrait;
use \packages\notice\views\note\delete as noticeDelete;
class delete extends noticeDelete{
	use viewTrait, formTrait;
	protected $note;
	function __beforeLoad(){
		$this->note = $this->getNote();
		$this->setTitle([
			translator::trans('notice'),
			translator::trans('notice.note.delete')
		]);
		navigation::active("settings/notice");
		$this->addBodyClass('notice');
	}
}
