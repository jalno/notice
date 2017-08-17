<?php
namespace themes\clipone\notice\notes;
use \packages\base\translator;
use \packages\userpanel\date;
use \packages\notice\note;
use \themes\clipone\utility;

trait labelsTrait{
	public function expireDateLabeled(note $note, string $format = 'Y/m/d'){
		if($note->expire_at > 0 and in_array($note->status, [note::active, note::suspended])){
			$time = date::time();
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
			echo date::format($format, $note->expire_at);
			echo("</span>");
		}else{
			echo('-');
		}
	}
	public function statusLabel(note $note):string{
		$statusClass = utility::switchcase($this->note->status, array(
			'label label-success' => note::active,
			'label label-warning lb' => note::deactive,
			'label label-danger' => note::suspended,
			'label label-warning' => note::in_process
		));
		$statusTxt = utility::switchcase($this->note->status, array(
			'active' => note::active,
			'deactive' => note::deactive,
			'suspended' => note::suspended,
			'in_process' => note::in_process
		));
		return "<span class=\"{$statusClass}\">".translator::trans($statusTxt)."</span>";
	}
}