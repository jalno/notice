<?php

namespace packages\notice\Note;

use packages\base\DB\DBObject;

class Param extends DBObject
{
    protected $dbTable = 'notice_notes_params';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'note' => ['type' => 'int', 'required' => true],
        'name' => ['type' => 'text', 'required' => true],
        'value' => ['type' => 'text', 'required' => true],
    ];
    protected $jsonFields = ['value'];
}
