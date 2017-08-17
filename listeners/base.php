<?php
namespace packages\notice\listeners;
use \packages\base\db;
use \packages\base\http;
use \packages\base\json;
use \packages\notice\note;
use \packages\userpanel\date;
use \packages\base\translator;
use \packages\base\view\error;
use \packages\base\db\parenthesis;
use \packages\notice\authorization;
use \packages\notice\authentication;
use \packages\base\frontend\events\throwDynamicData;
class base{
	public function beforeLoad(throwDynamicData $event){
		$user = authentication::getUser();
		if(!$user){
			return;
		}
		$view = $event->getView();
		$parents = $this->getParents($view);
		$parents[] = get_class($view);
		db::join('notice_notes_users', 'notice_notes_users.note=notice_notes.id', 'LEFT');
		db::join('notice_notes_usertypes', 'notice_notes_usertypes.note=notice_notes.id', 'LEFT');

		$note = new note();

			$parenthesis = new parenthesis();
			$parenthesis->where('notice_notes.view', $parents, 'in');
			$parenthesis->where('notice_notes.view', http::getURL(), 'equals', "OR");
		$note->where($parenthesis);

			$parenthesis = new parenthesis();
			$parenthesis->where('notice_notes_users.user', $user->id);
			$parenthesis->orWhere('notice_notes_usertypes.type', $user->type->id);
		$note->where($parenthesis);

			$parenthesis = new parenthesis();
			$parenthesis->where('notice_notes.create_at', date::time(), "<");
			$parenthesis->where('notice_notes.create_at', null, "is", "OR");
		$note->where($parenthesis);

			$parenthesis = new parenthesis();
			$parenthesis->where('notice_notes.start_time', date::format('H', date::time()), "<", "OR");
			$parenthesis->where('notice_notes.start_time', null, "is", "OR");
		$note->where($parenthesis);

			$parenthesis = new parenthesis();
			$parenthesis->where('notice_notes.end_time', date::format('H', date::time()), ">", "OR");
			$parenthesis->where('notice_notes.end_time', null, "is", "OR");
		$note->where($parenthesis);
		
			$parenthesis = new parenthesis();
			$parenthesis->where('notice_notes.expire_at', date::time(), ">", "OR");
			$parenthesis->where('notice_notes.expire_at', null, "is", "OR");
		$note->where($parenthesis);

		$note->where('notice_notes.status', note::active);
		$note->setQueryOption('DISTINCT');
		$nots = $note->get(null, ['notice_notes.*']);
		$notices = [
			'canAdd' => authorization::is_accessed('edit'),
			'notes' => []
		];
		foreach($nots as $note){
			if($note->param('show-option') == 'once' and $note->isClosed($user)){
				continue;
			}
			if($note->param('show-option') == 'closable' and $note->isClosed($user)){
				continue;
			}
			if($note->param('show-option') == 'once'){
				$note->close($user);
			}
			$notice = [
				'title' => $note->title,
				'content' => $note->content,
				'type' => $note->type,
				'params' => [
					'style' => $note->param('style'),
					'classes' => ['notice'],
					'canEdit' => authorization::is_accessed('edit')
				],
				'data' => [
					'note' => $note->id
				]
			];
			if($note->param('show-option') == 'closable'){
				$notice['params']['classes'][] = 'note-closable';
			}
			$notices['notes'][] = $notice;
		}
		$event->setData('packages_notice_notes', $notices);
	}
	private function getParents($obj){
		$result = [];
		$parent = get_parent_class($obj);
		if($parent){
			$result[] = $parent;
			foreach($this->getParents($parent) as $item){
				$result[] = $item;
			}
		}
		return $result;
	}
}
