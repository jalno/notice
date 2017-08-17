<?php
namespace packages\notice;
use \packages\base\db;
use \packages\userpanel\user;
use \packages\base\db\dbObject;
use \packages\notice\Paramable;
use \packages\userpanel\usertype;
class note extends dbObject{
    use Paramable;
	const active = 1;
	const deactive = 2;
	const alert = 'alert';
	const modal = 'modal';
	protected $dbTable = "notice_notes";
	protected $primaryKey = "id";
	protected $dbFields = [
        'view' => ['type' => 'text', 'required' => true],
        'type' => ['type' => 'text', 'required' => true],
        'content' => ['type' => 'text', 'required' => true],
        'create_at' => ['type' => 'int'],
        'start_time' => ['type' => 'int'],
        'end_time' => ['type' => 'int'],
        'expire_at' => ['type' => 'int'],
		'title' => ['type' => 'text', 'required' => true],
		'content' => ['type' => 'text', 'required' => true],
		'status' => ['type' => 'int', 'required' => true]
	];
    protected $relations = [
		'params' => ['hasMany', 'packages\\notice\\note\\param', 'note']
	];
	public function addUser(user $user){
		db::insert('notice_notes_users', [
			'user' => $user->id,
			'note' => $this->id
		]);
	}
	public function addUserType(usertype $usertype){
		db::insert('notice_notes_usertypes', [
			'type' => $usertype->id,
			'note' => $this->id
		]);
	}
	public function isClosed(user $user){
		db::where('user', $user->id);
		db::where('note', $this->id);
		return db::getValue('notice_notes_users', 'closed');
	}
	public function close(user $user){
		db::where('user', $user->id);
		db::where('note', $this->id);
		if(db::has('notice_notes_users')){
			db::where('user', $user->id);
			db::where('note', $this->id);
			db::update('notice_notes_users', [
				'closed' => true
			]);
		}else{
			db::insert('notice_notes_users', [
				'user' => $user->id,
				'note' => $this->id,
				'closed' => true
			]);
		}
	}
	public function getUsers(){
		db::where('note', $this->id);
		return db::get('notice_notes_users', null, 'user');
	}
	public function getTypes(){
		db::where('note', $this->id);
		return db::get('notice_notes_usertypes', null, 'type');
	}
	public function deleteUser(user $user = null){
		db::where('note', $this->id);
		if($user){
			db::where('user', $user->id);
		}
		db::delete('notice_notes_users');
	}
	public function deleteType(usertype $type = null){
		db::where('note', $this->id);
		if($type){
			db::where('type', $type->id);
		}
		db::delete('notice_notes_usertypes');
	}
}