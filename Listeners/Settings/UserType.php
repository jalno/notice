<?php
namespace packages\notice\Listeners\Settings;
use \packages\userpanel\UserType\Permissions;
class UserType{
	public function permissions_list(){
		$permissions = array(
			'search',
			'add',
			'delete',
			'edit'
		);
		foreach($permissions as $permission){
			Permissions::add('notice_'.$permission);
		}
	}
}
