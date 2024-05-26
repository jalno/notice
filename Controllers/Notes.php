<?php
namespace packages\notice\Controllers;
use \packages\base\DB;
use \packages\base\HTTP;
use \packages\base\NotFound;
use \packages\base\View\Error;
use \packages\base\DB\Parenthesis;
use \packages\base\Views\FormError;
use \packages\base\NoViewException;
use \packages\base\InputValidation;
use \packages\base\DB\DuplicateRecord;
use \packages\userpanel;
use \packages\userpanel\User;
use \packages\userpanel\Date;
use \packages\notice\View;
use \packages\notice\Note;
use \packages\notice\Controller;
use \packages\userpanel\UserType;
use \packages\notice\Authorization;
use \packages\notice\Authentication;
use \packages\notice\Events\Views;
use packages\notice\Views\Note as Noteview;
class Notes extends Controller{
	protected $authentication = true;
	public function search(){
		Authorization::haveOrFail('search');
		$view = View::byName(Noteview\Search::class);
		$views = new Views();
		$views->trigger();
		$view->setViews($views);
		$note = new Note();
		$inputsRules = [
			'id' => [
				'type' => 'number',
				'optional' => true,
				'empty' => true
			],
			'view' => [
				'optional' => true,
				'empty' => true
			],
			'type' => [
				'type' => 'string',
				'optional' => true,
				'empty' => true
			],
			'status' => [
				'type' => 'number',
				'optional' => true,
				'empty' => true,
				'values' => [
					Note::active,
					Note::deactive,
				]
			],
			'word' => [
				'type' => 'string',
				'optional' => true,
				'empty' => true
			],
			'comparison' => [
				'values' => ['equals', 'startswith', 'contains'],
				'default' => 'contains',
				'optional' => true
			]
		];
		$this->response->setStatus(true);
		try{
			$inputs = $this->checkinputs($inputsRules);
			foreach(['id', 'view', 'type', 'status'] as $item){
				if(isset($inputs[$item]) and $inputs[$item]){
					$comparison = $inputs['comparison'];
					if(in_array($item, ['id', 'status'])){
						$comparison = 'equals';
					}
					$note->where($item, $inputs[$item], $comparison);
				}
			}
			if(isset($inputs['word']) and $inputs['word']){
				$parenthesis = new Parenthesis();
				foreach(['view', 'type'] as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where($item, $inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$note->where($parenthesis);
			}
		}catch(InputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$note->pageLimit = $this->items_per_page;
		$notes = $note->paginate($this->page, 'notice_notes.*');
		$view->setDataList($notes);
		$view->setPaginate($this->page, DB::totalCount(), $this->items_per_page);
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		Authorization::haveOrFail('add');
		$view = View::byName(NoteView\Add::class);
		$inputsRules = [
			'address' => [
				'optional' => true
			]
		];
		$inputs = $this->checkinputs($inputsRules);
		$views = new Views();
		$views->trigger();
		if(isset($inputs['address'])){
			$view->setDataForm('selection', 'user');
			$view->setDataForm($inputs['address'], 'address');
			$view->setDataForm($inputs['address'], 'view');
			$eventview = new Views\View($inputs['address']);
			$eventview->setView($inputs['address']);
			$views->addView($eventview);
		}else{
			$view->setDataForm('kind', 'view');
		}
		$view->setViews($views);
		$types = Authorization::childrenTypes();
		$view->setUserTypes(UserType::where('id', $types, 'in')->get());
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function store(){
		Authorization::haveOrFail('add');
		$view = View::byName(NoteView\Add::class);
		$views = new Views();
		$views->trigger();
		$view->setViews($views);
		$types = Authorization::childrenTypes();
		$view->setUserTypes(UserType::where('id', $types, 'in')->get());
		$inputsRules = [
			'kind' => [
				'type' => 'string',
				'optional' => true,
				'values' => ['view', 'address'],
				'default' => 'view'
			],
			'view' => [],
			'address' => [
				'optional' => true,
				'empty' => true
			],
			'type' => [
				'type' => 'string',
				'values' => [Note::alert, Note::modal]
			],
			'status' => [
				'type' => 'number',
				'values' => [Note::active, Note::deactive]
			],
			'create_at' => [
				'type' => 'date',
				'optional' => true,
				'empty' => true
			],
			'expire_at' => [
				'type' => 'date',
				'optional' => true,
				'empty' => true
			],
			'time' => [],
			'style' => [
				'type' => 'string',
				'values' => [Error::SUCCESS, Error::NOTICE, Error::WARNING, Error::FATAL],
				'optional' => true
			],
			'user' => [
				'type' => 'string',
				'values' => ['all', 'selection']
			],
			'users' => [
				'optional' => true
			],
			'usertypes' => [
				'optional' => true
			],
			'show-option' => [
				'type' => 'string',
				'valeus' => ['once', 'closable', 'expire_at']
			],
			'title' => [
				'type' => 'string'
			],
			'content' => []
		];
		$this->response->setStatus(true);
		try{
			$inputs = $this->checkinputs($inputsRules);
			if(isset($inputs['expire_at']) and !$inputs['expire_at']){
				unset($inputs['expire_at']);
			}
			if(isset($inputs['create_at']) and !$inputs['create_at']){
				unset($inputs['create_at']);
			}
			if(isset($inputs['create_at'])){
				$inputs['create_at'] = Date::strtotime($inputs['create_at']);
			}
			if(isset($inputs['expire_at'])){
				$inputs['expire_at'] = Date::strtotime($inputs['expire_at']);
			}
			if($inputs['kind'] == 'view'){
				View::byName($inputs['view']);
			}elseif(!isset($inputs['address'])){
				throw new InputValidation('address');
			}
			switch($inputs['type']){
				case(Note::alert):
					if(!isset($inputs['style'])){
						throw new InputValidation('type');
					}
					break;
			}
			if($inputs['create_at'] <= 0){
				throw new InputValidation('create_at');
			}
			if(isset($inputs['expire_at'])){
				if($inputs['expire_at'] <= 0){
					throw new InputValidation('expire_at');
				}
			}
			if(!is_array($inputs['time'])){
				throw new InputValidation('time');
			}
			foreach(['start', 'end'] as $item){
				if(!isset($inputs['time'][$item])){
					throw new InputValidation("time[{$item}]");
				}
			}
			if($inputs['time']['start'] > 23){
				throw new InputValidation("time['start]");
			}
			if($inputs['time']['end'] < 0){
				throw new InputValidation("time['end]");
			}
			switch($inputs['user']){
				case('all'):
					unset($inputs['users']);
					$inputs['usertypes'] = UserType::get();
					break;
				case('selection'):
					if(isset($inputs['usertypes']) and !$inputs['usertypes']){
						unset($inputs['usertypes']);
					}
					if(isset($inputs['usertypes']) and !is_array($inputs['usertypes'])){
						throw new InputValidation('usertypes');
					}
					if(isset($inputs['usertypes'])){
						foreach($inputs['usertypes'] as $key => $type){
							if(!$inputs['usertypes'][$key] = UserType::byId($type)){
								throw new InputValidation("usertypes[{$key}]");
							}
						}
					}
					if(!isset($inputs['users']) and !isset($inputs['usertypes'])){
						throw new InputValidation('user');
					}
					if(isset($inputs['users']) and !is_array($inputs['users'])){
						throw new InputValidation('user');
					}
					if(isset($inputs['users'])){
						foreach($inputs['users'] as $key => $user){
							if(!$inputs['users'][$key] = User::byId($user)){
								throw new InputValidation("users[{$key}]");
							}
						}
					}
					break;
			}
			if($inputs['show-option'] == 'expire_at' and !isset($inputs['expire_at'])){
				throw new InputValidation('show-option');
			}
			$note = new Note();
			if($inputs['kind'] == 'address'){
				$inputs['view'] = $inputs['address'];
				$note->setParam('view-type', 'address');
			}
			$note->view = $inputs['view'];
			if(isset($inputs['create_at'])){
				$note->create_at = $inputs['create_at'];
			}
			if(isset($inputs['expire_at'])){
				$note->expire_at = $inputs['expire_at'];
			}
			$note->start_time = $inputs['time']['start'];
			$note->end_time = $inputs['time']['end'];
			foreach(['type', 'title', 'content', 'status'] as $item){
				$note->$item = $inputs[$item];
			}
			$note->setParam('user', $inputs['user']);
			$note->setParam('show-option', $inputs['show-option']);
			if(isset($inputs['style'])){
				$note->setParam('style', $inputs['style']);
			}
			$note->save();
			if(isset($inputs['users'])){
				foreach($inputs['users'] as $user){
					$note->addUser($user);
				}
			}
			if(isset($inputs['usertypes'])){
				foreach($inputs['usertypes'] as $type){
					$note->addUserType($type);
				}
			}
			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('settings/notice/notes/edit/'.$note->id));
		}catch(NoViewException $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}catch(InputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputsRules));
		$this->response->setView($view);
		return $this->response;
	}
	public function close(){
		$this->response->setStatus(false);
		$inputsRules = [
			'note' => [
				'type' => 'number'
			]
		];
		$inputs = $this->checkinputs($inputsRules);
		if(!$note = Note::byId($inputs['note'])){
			return $this->response;
		}
		$note->close(Authentication::getUser());
		$this->response->setStatus(true);
		return $this->response;
	}
	public function edit($data){
		Authorization::haveOrFail('edit');
		$note = Note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = View::byName(NoteView\Edit::class);
		$view->setNote($note);
		$views = new Views();
		$views->trigger();
		if($note->param('view-type') == 'address'){
			$view->setDataForm($note->view, 'address');
			$view->setDataForm($note->view, 'view');
			$eventview = new Views\View($note->view);
			$eventview->setView($note->view);
			$views->addView($eventview);
		}
		$view->setViews($views);
		$types = Authorization::childrenTypes();
		$view->setUserTypes(UserType::where('id', $types, 'in')->get());
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function update($data){
		Authorization::haveOrFail('edit');
		$note = Note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = View::byName(NoteView\Edit::class);
		$views = new Views();
		$views->trigger();
		if($note->param('type') == 'address'){
			$view->setDataForm($note->view, 'address');
			$view->setDataForm($note->view, 'view');
			$eventview = new Views\View($note->view);
			$eventview->setView($note->view);
			$views->addView($eventview);
		}
		$view->setViews($views);
		$types = Authorization::childrenTypes();
		$view->setUserTypes(UserType::where('id', $types, 'in')->get());
		$inputsRules = [
			'kind' => [
				'type' => 'string',
				'optional' => true,
				'values' => ['view', 'address'],
				'default' => 'view'
			],
			'view' => [],
			'address' => [
				'optional' => true,
				'empty' => true
			],
			'type' => [
				'type' => 'string',
				'values' => [Note::alert, Note::modal]
			],
			'status' => [
				'type' => 'number',
				'values' => [Note::active, Note::deactive]
			],
			'create_at' => [
				'type' => 'date',
				'optional' => true,
				'empty' => true
			],
			'expire_at' => [
				'type' => 'date',
				'optional' => true,
				'empty' => true
			],
			'time' => [],
			'style' => [
				'type' => 'string',
				'values' => [Error::SUCCESS, Error::NOTICE, Error::WARNING, Error::FATAL],
				'optional' => true
			],
			'user' => [
				'type' => 'string',
				'values' => ['all', 'selection']
			],
			'users' => [
				'optional' => true
			],
			'usertypes' => [
				'optional' => true
			],
			'show-option' => [
				'type' => 'string',
				'valeus' => ['once', 'closable', 'expire_at']
			],
			'title' => [
				'type' => 'string'
			],
			'content' => []
		];
		$this->response->setStatus(true);
		try{
			$inputs = $this->checkinputs($inputsRules);
			if(isset($inputs['expire_at']) and !$inputs['expire_at']){
				unset($inputs['expire_at']);
			}
			if(isset($inputs['create_at']) and !$inputs['create_at']){
				unset($inputs['create_at']);
			}
			if(isset($inputs['create_at'])){
				$inputs['create_at'] = Date::strtotime($inputs['create_at']);
			}
			if(isset($inputs['expire_at'])){
				$inputs['expire_at'] = Date::strtotime($inputs['expire_at']);
			}
			if($inputs['kind'] == 'view'){
				View::byName($inputs['view']);
			}elseif(!isset($inputs['address'])){
				throw new InputValidation('address');
			}
			switch($inputs['type']){
				case(note::alert):
					if(!isset($inputs['style'])){
						throw new InputValidation('type');
					}
					break;
			}
			if($inputs['create_at'] <= 0){
				throw new InputValidation('create_at');
			}
			if(isset($inputs['expire_at'])){
				if($inputs['expire_at'] <= 0){
					throw new InputValidation('expire_at');
				}
			}
			if(!is_array($inputs['time'])){
				throw new InputValidation('time');
			}
			foreach(['start', 'end'] as $item){
				if(!isset($inputs['time'][$item])){
					throw new InputValidation("time[{$item}]");
				}
			}
			if($inputs['time']['start'] > 23){
				throw new InputValidation("time['start]");
			}
			if($inputs['time']['end'] < 0){
				throw new InputValidation("time['end]");
			}
			switch($inputs['user']){
				case('all'):
					unset($inputs['users']);
					$inputs['usertypes'] = UserType::get();
					break;
				case('selection'):
					if(isset($inputs['usertypes']) and !$inputs['usertypes']){
						unset($inputs['usertypes']);
					}
					if(isset($inputs['usertypes']) and !is_array($inputs['usertypes'])){
						throw new InputValidation('usertypes');
					}
					if(isset($inputs['usertypes'])){
						foreach($inputs['usertypes'] as $key => $type){
							if(!$inputs['usertypes'][$key] = UserType::byId($type)){
								throw new InputValidation("usertypes[{$key}]");
							}
						}
					}
					if(!isset($inputs['users']) and !isset($inputs['usertypes'])){
						throw new InputValidation('user');
					}
					if(isset($inputs['users']) and !is_array($inputs['users'])){
						throw new InputValidation('user');
					}
					if(isset($inputs['users'])){
						foreach($inputs['users'] as $key => $user){
							if(!$inputs['users'][$key] = User::byId($user)){
								throw new InputValidation("users[{$key}]");
							}
						}
					}
					break;
			}
			if($inputs['show-option'] == 'expire_at' and !isset($inputs['expire_at'])){
				throw new InputValidation('show-option');
			}
			if($inputs['kind'] == 'address'){
				$inputs['view'] = $inputs['address'];
				$note->setParam('view-type', 'address');
			}
			$note->deleteUser();
			$note->deleteUserType();
			$note->view = $inputs['view'];
			if(isset($inputs['create_at'])){
				$note->create_at = $inputs['create_at'];
			}
			if(isset($inputs['expire_at'])){
				$note->expire_at = $inputs['expire_at'];
			}
			$note->start_time = $inputs['time']['start'];
			$note->end_time = $inputs['time']['end'];
			foreach(['type', 'title', 'content', 'status'] as $item){
				$note->$item = $inputs[$item];
			}
			$note->setParam('user', $inputs['user']);
			$note->setParam('show-option', $inputs['show-option']);
			if(isset($inputs['style'])){
				$note->setParam('style', $inputs['style']);
			}
			$note->save();
			if(isset($inputs['users'])){
				foreach($inputs['users'] as $user){
					$note->addUser($user);
				}
			}
			if(isset($inputs['usertypes'])){
				foreach($inputs['usertypes'] as $type){
					$note->addUserType($type);
				}
			}
		}catch(NoViewException $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}catch(InputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputsRules));
		$this->response->setView($view);
		return $this->response;
	}
	public function delete($data){
		Authorization::haveOrFail('delete');
		$note = Note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = View::byName(NoteView\Delete::class);
		$view->setNote($note);
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function terminate($data){
		Authorization::haveOrFail('delete');
		$note = Note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = View::byName(NoteView\Delete::class);
		$view->setNote($note);
		try{
			$note->delete();
			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('settings/notice/notes'));
		}catch(InputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$this->response->setView($view);
		return $this->response;
	}
}
