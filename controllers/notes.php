<?php
namespace packages\notice\controllers;
use \packages\base\db;
use \packages\base\http;
use \packages\base\NotFound;
use \packages\base\view\error;
use \packages\base\db\parenthesis;
use \packages\base\views\FormError;
use \packages\base\NoViewException;
use \packages\base\inputValidation;
use \packages\base\db\duplicateRecord;
use \packages\userpanel;
use \packages\userpanel\user;
use \packages\userpanel\date;
use \packages\notice\view;
use \packages\notice\note;
use \packages\notice\controller;
use \packages\userpanel\usertype;
use \packages\notice\authorization;
use \packages\notice\authentication;
use \packages\notice\events\views;
use packages\notice\views\note as noteview;
class notes extends controller{
	protected $authentication = true;
	public function search(){
		authorization::haveOrFail('search');
		$view = view::byName(noteview\search::class);
		$views = new views();
		$views->trigger();
		$view->setViews($views);
		$note = new note();
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
					note::active,
					note::deactive,
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
				$parenthesis = new parenthesis();
				foreach(['view', 'type'] as $item){
					if(!isset($inputs[$item]) or !$inputs[$item]){
						$parenthesis->where($item, $inputs['word'], $inputs['comparison'], 'OR');
					}
				}
				$note->where($parenthesis);
			}
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$note->pageLimit = $this->items_per_page;
		$notes = $note->paginate($this->page, 'notice_notes.*');
		$view->setDataList($notes);
		$view->setPaginate($this->page, db::totalCount(), $this->items_per_page);
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function add(){
		authorization::haveOrFail('add');
		$view = view::byName(noteview\add::class);
		$inputsRules = [
			'address' => [
				'optional' => true
			]
		];
		$inputs = $this->checkinputs($inputsRules);
		$views = new views();
		$views->trigger();
		if(isset($inputs['address'])){
			$view->setDataForm('selection', 'user');
			$view->setDataForm($inputs['address'], 'address');
			$view->setDataForm($inputs['address'], 'view');
			$eventview = new views\view($inputs['address']);
			$eventview->setView($inputs['address']);
			$views->addView($eventview);
		}else{
			$view->setDataForm('kind', 'view');
		}
		$view->setViews($views);
		$types = authorization::childrenTypes();
		$view->setUserTypes(usertype::where('id', $types, 'in')->get());
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function store(){
		authorization::haveOrFail('add');
		$view = view::byName(noteview\add::class);
		$views = new views();
		$views->trigger();
		$view->setViews($views);
		$types = authorization::childrenTypes();
		$view->setUserTypes(usertype::where('id', $types, 'in')->get());
		$inputsRules = [
			'kind' => [
				'type' => 'string',
				'optional' => true,
				'values' => ['view', 'address'],
				'default' => 'view'
			],
			'view' => [],
			'address' => [
				'optional' => true
			],
			'type' => [
				'type' => 'string',
				'values' => [note::alert, note::modal]
			],
			'status' => [
				'type' => 'number',
				'values' => [note::active, note::deactive]
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
				'values' => [error::SUCCESS, error::NOTICE, error::WARNING, error::FATAL],
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
				$inputs['create_at'] = date::strtotime($inputs['create_at']);
			}
			if(isset($inputs['expire_at'])){
				$inputs['expire_at'] = date::strtotime($inputs['expire_at']);
			}
			if($inputs['kind'] == 'view'){
				view::byName($inputs['view']);
			}elseif(!isset($inputs['address'])){
				throw new inputValidation('address');
			}
			switch($inputs['type']){
				case(note::alert):
					if(!isset($inputs['style'])){
						throw new inputValidation('type');
					}
					break;
			}
			if($inputs['create_at'] <= 0){
				throw new inputValidation('create_at');
			}
			if(isset($inputs['expire_at'])){
				if($inputs['expire_at'] <= 0){
					throw new inputValidation('expire_at');
				}
			}
			if(!is_array($inputs['time'])){
				throw new inputValidation('time');
			}
			foreach(['start', 'end'] as $item){
				if(!isset($inputs['time'][$item])){
					throw new inputValidation("time[{$item}]");
				}
			}
			if($inputs['time']['start'] > 23){
				throw new inputValidation("time['start]");
			}
			if($inputs['time']['end'] < 0){
				throw new inputValidation("time['end]");
			}
			switch($inputs['user']){
				case('all'):
					unset($inputs['users']);
					$inputs['usertypes'][] = usertype::get();
					break;
				case('selection'):
					if(isset($inputs['usertypes']) and !$inputs['usertypes']){
						unset($inputs['usertypes']);
					}
					if(isset($inputs['usertypes']) and !is_array($inputs['usertypes'])){
						throw new inputValidation('usertypes');
					}
					if(isset($inputs['usertypes'])){
						foreach($inputs['usertypes'] as $key => $type){
							if(!$inputs['usertypes'][$key] = usertype::byId($type)){
								throw new inputValidation("usertypes[{$key}]");
							}
						}
					}
					if(!isset($inputs['users']) and !isset($inputs['usertypes'])){
						throw new inputValidation('user');
					}
					if(isset($inputs['users']) and !is_array($inputs['users'])){
						throw new inputValidation('user');
					}
					if(isset($inputs['users'])){
						foreach($inputs['users'] as $key => $user){
							if(!$inputs['users'][$key] = user::byId($user)){
								throw new inputValidation("users[{$key}]");
							}
						}
					}
					break;
			}
			if($inputs['show-option'] == 'expire_at' and !isset($inputs['expire_at'])){
				throw new inputValidation('show-option');
			}
			$note = new note();
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
			$this->response->Go(userpanel\url('settings/notices/notes/'.$note->id));
		}catch(NoViewException $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}catch(inputValidation $error){
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
		if(!$note = note::byId($inputs['note'])){
			return $this->response;
		}
		$note->close(authentication::getUser());
		$this->response->setStatus(true);
		return $this->response;
	}
	public function edit($data){
		authorization::haveOrFail('edit');
		$note = note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = view::byName(noteview\edit::class);
		$view->setNote($note);
		$views = new views();
		$views->trigger();
		if($note->param('view-type') == 'address'){
			$view->setDataForm($note->view, 'address');
			$view->setDataForm($note->view, 'view');
			$eventview = new views\view($note->view);
			$eventview->setView($note->view);
			$views->addView($eventview);
		}
		$view->setViews($views);
		$types = authorization::childrenTypes();
		$view->setUserTypes(usertype::where('id', $types, 'in')->get());
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function update($data){
		authorization::haveOrFail('edit');
		$note = note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = view::byName(noteview\edit::class);
		$views = new views();
		$views->trigger();
		if($note->param('type') == 'address'){
			$view->setDataForm($note->view, 'address');
			$view->setDataForm($note->view, 'view');
			$eventview = new views\view($note->view);
			$eventview->setView($note->view);
			$views->addView($eventview);
		}
		$view->setViews($views);
		$types = authorization::childrenTypes();
		$view->setUserTypes(usertype::where('id', $types, 'in')->get());
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
				'values' => [note::alert, note::modal]
			],
			'status' => [
				'type' => 'number',
				'values' => [note::active, note::deactive]
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
				'values' => [error::SUCCESS, error::NOTICE, error::WARNING, error::FATAL],
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
				$inputs['create_at'] = date::strtotime($inputs['create_at']);
			}
			if(isset($inputs['expire_at'])){
				$inputs['expire_at'] = date::strtotime($inputs['expire_at']);
			}
			if($inputs['kind'] == 'view'){
				view::byName($inputs['view']);
			}elseif(!isset($inputs['address'])){
				throw new inputValidation('address');
			}
			switch($inputs['type']){
				case(note::alert):
					if(!isset($inputs['style'])){
						throw new inputValidation('type');
					}
					break;
			}
			if($inputs['create_at'] <= 0){
				throw new inputValidation('create_at');
			}
			if(isset($inputs['expire_at'])){
				if($inputs['expire_at'] <= 0){
					throw new inputValidation('expire_at');
				}
			}
			if(!is_array($inputs['time'])){
				throw new inputValidation('time');
			}
			foreach(['start', 'end'] as $item){
				if(!isset($inputs['time'][$item])){
					throw new inputValidation("time[{$item}]");
				}
			}
			if($inputs['time']['start'] > 23){
				throw new inputValidation("time['start]");
			}
			if($inputs['time']['end'] < 0){
				throw new inputValidation("time['end]");
			}
			switch($inputs['user']){
				case('all'):
					unset($inputs['users']);
					$inputs['usertypes'] = usertype::get();
					break;
				case('selection'):
					if(isset($inputs['usertypes']) and !$inputs['usertypes']){
						unset($inputs['usertypes']);
					}
					if(isset($inputs['usertypes']) and !is_array($inputs['usertypes'])){
						throw new inputValidation('usertypes');
					}
					if(isset($inputs['usertypes'])){
						foreach($inputs['usertypes'] as $key => $type){
							if(!$inputs['usertypes'][$key] = usertype::byId($type)){
								throw new inputValidation("usertypes[{$key}]");
							}
						}
					}
					if(!isset($inputs['users']) and !isset($inputs['usertypes'])){
						throw new inputValidation('user');
					}
					if(isset($inputs['users']) and !is_array($inputs['users'])){
						throw new inputValidation('user');
					}
					if(isset($inputs['users'])){
						foreach($inputs['users'] as $key => $user){
							if(!$inputs['users'][$key] = user::byId($user)){
								throw new inputValidation("users[{$key}]");
							}
						}
					}
					break;
			}
			if($inputs['show-option'] == 'expire_at' and !isset($inputs['expire_at'])){
				throw new inputValidation('show-option');
			}
			if($inputs['kind'] == 'address'){
				$inputs['view'] = $inputs['address'];
				$note->setParam('view-type', 'address');
			}
			$note->deleteUser();
			$note->deleteType();
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
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$view->setDataForm($this->inputsvalue($inputsRules));
		$this->response->setView($view);
		return $this->response;
	}
	public function delete($data){
		authorization::haveOrFail('delete');
		$note = note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = view::byName(noteview\delete::class);
		$view->setNote($note);
		$this->response->setStatus(true);
		$this->response->setView($view);
		return $this->response;
	}
	public function terminate($data){
		authorization::haveOrFail('delete');
		$note = note::byId($data['note']);
		if(!$note){
			throw new NotFound();
		}
		$view = view::byName(noteview\delete::class);
		$view->setNote($note);
		try{
			$note->delete();
			$this->response->setStatus(true);
			$this->response->Go(userpanel\url('settings/notice/notes'));
		}catch(inputValidation $error){
			$view->setFormError(FormError::fromException($error));
			$this->response->setStatus(false);
		}
		$this->response->setView($view);
		return $this->response;
	}
}
