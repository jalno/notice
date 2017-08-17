<?php
namespace packages\notice\views\note;
use \packages\notice\events\views;
use \packages\notice\authorization;
use \packages\notice\views\listview;
use \packages\base\views\traits\form as formTrait;
class search extends listview{
	use formTrait;
	protected $canAdd;
	protected $canEdit;
	protected $canDel;
	static protected $navigation;
	function __construct(){
		$this->canAdd = authorization::is_accessed('add');
		$this->canEdit = authorization::is_accessed('edit');
		$this->canDel = authorization::is_accessed('delete');
	}
	public function getNoteLists(){
		return $this->dataList;
	}
	public static function onSourceLoad(){
		self::$navigation = authorization::is_accessed('search');
	}
	public function setViews(views $views){
		$this->setData($views, 'views');
	}
	protected function getViews():views{
		return $this->getData('views');
	}
}
