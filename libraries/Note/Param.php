<?php
namespace packages\notice\Note;
use packages\base\DB\DBObject;

class Param extends DBObject{
	protected $dbTable = "notice_notes_params";
	protected $primaryKey = "id";
	protected $dbFields = array(
        'note' => array('type' => 'int', 'required' => true),
		'name' => array('type' => 'text', 'required' => true),
		'value' => array('type' => 'text', 'required' => true),
    );
	protected $jsonFields = ['value'];
}
