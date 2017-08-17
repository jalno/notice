<?php
namespace packages\notice\listeners\settings;
use \packages\userpanel\usertype\permissions;
class usertype{
	public function permissions_list(){
		$permissions = array(
			'search',
			'add',
			'delete',
			'edit'
		);
		foreach($permissions as $permission){
			permissions::add('notice_'.$permission);
		}
	}
}
