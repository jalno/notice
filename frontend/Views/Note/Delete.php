<?php

namespace themes\clipone\Views\Notice\Note;

use packages\base\Translator;
use packages\notice\Views\Note\Delete as NoticeDelete;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Delete extends NoticeDelete
{
    use ViewTrait;
    use FormTrait;
    protected $note;

    public function __beforeLoad()
    {
        $this->note = $this->getNote();
        $this->setTitle([
            Translator::trans('notice'),
            Translator::trans('notice.note.delete'),
        ]);
        Navigation::active('settings/notice');
        $this->addBodyClass('notice');
    }
}
