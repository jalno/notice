<?php
namespace packages\notice\listeners;

use packages\base\{DB, view\Error, http, JSON, DB\Parenthesis, Translator, frontend\events\ThrowDynamicData};
use packages\notice\{Authentication, Authorization, Note};
use packages\userpanel\{Date};

class Base {
	public function beforeLoad(ThrowDynamicData $event) {
		$notices = array(
			'canAdd' => false,
			'notes' => array(),
		);
		$event->setData('packages_notice_notes', $notices);
		$user = Authentication::getUser();
		if (!$user) {
			return;
		}
		$notices['canAdd'] = Authorization::is_accessed('edit');
		$view = $event->getView();
		$parents = $this->getParents($view);
		$parents[] = get_class($view);
		$time = Date::time();
		$hour = Date::format('H', $time);
		db::join('notice_notes_users', 'notice_notes_users.note=notice_notes.id', 'LEFT');
		db::join('notice_notes_usertypes', 'notice_notes_usertypes.note=notice_notes.id', 'LEFT');

		$notes = new note();

			$parenthesis = new Parenthesis();
			$parenthesis->where('notice_notes.view', $parents, 'in');
			$parenthesis->where('notice_notes.view', http::getURL(), 'equals', "OR");
		$notes->where($parenthesis);

			$parenthesis = new Parenthesis();
			$parenthesis->where('notice_notes_users.user', $user->id);
			$parenthesis->orWhere('notice_notes_usertypes.type', $user->type->id);
		$notes->where($parenthesis);

			$parenthesis = new Parenthesis();
			$parenthesis->where('notice_notes.create_at', $time, "<");
			$parenthesis->where('notice_notes.create_at', null, "is", "OR");
		$notes->where($parenthesis);

			$parenthesis = new Parenthesis();
			$parenthesis->where('notice_notes.start_time', $hour, "<", "OR");
			$parenthesis->where('notice_notes.start_time', null, "is", "OR");
		$notes->where($parenthesis);

			$parenthesis = new Parenthesis();
			$parenthesis->where('notice_notes.end_time', $hour, ">", "OR");
			$parenthesis->where('notice_notes.end_time', null, "is", "OR");
		$notes->where($parenthesis);
		
			$parenthesis = new Parenthesis();
			$parenthesis->where('notice_notes.expire_at', $time, ">", "OR");
			$parenthesis->where('notice_notes.expire_at', null, "is", "OR");
		$notes->where($parenthesis);

		$notes->where('notice_notes.status', note::active);
		$notes->setQueryOption('DISTINCT');
		$notes = $notes->get(null, ['notice_notes.*']);

		foreach ($notes as $note) {
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
