<?php

namespace themes\clipone\Views\Notice\Note;

use packages\base\Frontend\Theme;
use packages\base\Json;
use packages\base\Translator;
use packages\notice\Note;
use packages\notice\Views\Note\Edit as NoteEdit;
use packages\userpanel\User;
use packages\userpanel\UserType;
use themes\clipone\Navigation;
use themes\clipone\Views\FormTrait;
use themes\clipone\ViewTrait;

class Edit extends NoteEdit
{
    use ViewTrait;
    use FormTrait;
    protected $note;

    public function __beforeLoad()
    {
        $this->note = $this->getNote();
        $this->setTitle([
            Translator::trans('notice'),
            Translator::trans('notice.note.edit'),
        ]);
        Navigation::active('settings/notice');
        $this->addBodyClass('notice');
        $this->addBodyClass('edit-note');
        $this->addAssets();
    }

    public function addAssets()
    {
        $this->addJSFile(Theme::url('assets/plugins/ckeditor/ckeditor.js'));
    }

    protected function getStatusForSelect(): array
    {
        return [
            [
                'title' => Translator::trans('notice.note.status.active'),
                'value' => Note::active,
            ],
            [
                'title' => Translator::trans('notice.note.status.deactive'),
                'value' => Note::deactive,
            ],
        ];
    }

    protected function getTypeForSelect(): array
    {
        return [
            [
                'title' => Translator::trans('notice.note.type.'.Note::alert),
                'value' => Note::alert,
            ],
            [
                'title' => Translator::trans('notice.note.type.'.Note::modal),
                'value' => Note::modal,
            ],
        ];
    }

    protected function getViewsForSelect(): array
    {
        $views = [];
        foreach ($this->getViews()->get() as $view) {
            $translator = Translator::trans('notice.note.view.name.'.$view->getName());
            $views[] = [
                'title' => $translator ? $translator : $view->getName(),
                'value' => $view->getView(),
                'data' => [
                    'custom' => ($address = $this->getDataForm('address') and $address == $view->getView()),
                ],
            ];
        }

        return $views;
    }

    private function getUserTypeParents(UserType $type): string
    {
        $parnets = [];
        if ($type->parents) {
            foreach ($type->parents as $parent) {
                $parnets[] = $parent->parent;
            }
        }

        return json\encode($parnets);
    }

    protected function getUserTypesForSelect(): array
    {
        $types = [];
        foreach ($this->getUserTypes() as $type) {
            $types[] = [
                'label' => $type->title,
                'value' => $type->id,
                'data' => [
                    'parent' => $this->getUserTypeParents($type),
                ],
            ];
        }

        return $types;
    }

    protected function getUsersForCheck(): array
    {
        $users = [];
        foreach ($this->note->getUsers() as $user) {
            $users[] = User::byId($user['user']);
        }

        return $users;
    }
}
