<?php
namespace themes\clipone\Notice\Notes;
use \packages\base\Translator;
use \packages\userpanel\Date;
use \packages\notice\Note;
use \themes\clipone\Utility;

trait LabelsTrait{
	public function expireDateLabeled(Note $note, string $format = 'Y/m/d'){
		if($note->expire_at > 0 and in_array($note->status, [Note::active, Note::suspended])){
			$time = Date::time();
			if($note->expire_at - $time < -604800){
				echo("<span>");
			}elseif($note->expire_at - $time < 0){
				echo("<span class=\"label label-inverse\">");
			}elseif($note->expire_at - $time < 259200){
				echo("<span class=\"label label-danger\">");
			}elseif($note->expire_at - $time < 604800){
				echo("<span class=\"label label-warning\">");
			}else{
				echo("<span>");
			}
			echo Date::format($format, $note->expire_at);
			echo("</span>");
		}else{
			echo('-');
		}
	}
	public function statusLabel(Note $note):string{
		$statusClass = Utility::switchcase($this->note->status, array(
			'label label-success' => Note::active,
			'label label-warning lb' => Note::deactive,
			'label label-danger' => Note::suspended,
			'label label-warning' => Note::in_process
		));
		$statusTxt = Utility::switchcase($this->note->status, array(
			'active' => Note::active,
			'deactive' => Note::deactive,
			'suspended' => Note::suspended,
			'in_process' => Note::in_process
		));
		return "<span class=\"{$statusClass}\">".Translator::trans($statusTxt)."</span>";
	}
}