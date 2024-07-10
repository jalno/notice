<?php

namespace packages\notice\Views\Note;

use packages\notice\Events\Views;
use packages\notice\Views\Form;

class Add extends Form
{
    public function setViews(Views $views)
    {
        $this->setData($views, 'views');
    }

    protected function getViews(): Views
    {
        return $this->getData('views');
    }

    public function setUserTypes(array $types)
    {
        $this->setData($types, 'types');
    }

    protected function getUserTypes(): array
    {
        return $this->getData('types');
    }
}
