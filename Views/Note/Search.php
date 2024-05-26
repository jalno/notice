<?php
namespace packages\notice\Views\Note;
use \packages\notice\Events\Views;
use \packages\notice\Authorization;
use \packages\notice\Views\ListView;
use \packages\base\Views\Traits\Form as FormTrait;
class Search extends ListView{
	use FormTrait;
	protected $canAdd;
	protected $canEdit;
	protected $canDel;
	static protected $navigation;
	function __construct(){
		$this->canAdd = Authorization::is_accessed('add');
		$this->canEdit = Authorization::is_accessed('edit');
		$this->canDel = Authorization::is_accessed('delete');
	}
	public function getNoteLists(){
		return $this->dataList;
	}
	public static function onSourceLoad(){
		self::$navigation = Authorization::is_accessed('search');
	}
	public function setViews(Views $views){
		$this->setData($views, 'views');
	}
	protected function getViews():Views{
		return $this->getData('views');
	}
}
