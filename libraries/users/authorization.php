<?php
namespace packages\notice;
use \packages\userpanel\authorization as UserPanelAuthorization;
use \packages\userpanel\authentication;
class authorization extends UserPanelAuthorization{
	static function is_accessed($permission, $prefix = 'notice'){
		return parent::is_accessed($permission, $prefix);
	}
	static function haveOrFail($permission, $prefix = 'notice'){
		parent::haveOrFail($permission, $prefix);
	}
}
