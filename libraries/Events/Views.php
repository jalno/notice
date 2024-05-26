<?php

namespace packages\notice\Events;

use packages\base\Event;
use packages\notice\Events\Views\View;

class Views extends Event
{
    private $views = [];

    public function addView(View $view)
    {
        $this->views[$view->getName()] = $view;
    }

    public function getViewNames(): array
    {
        return array_keys($this->views);
    }

    public function getByName(string $name)
    {
        return isset($this->views[$name]) ? $this->views[$name] : null;
    }

    public function getByView(string $view)
    {
        foreach ($this->views as $obj) {
            if ($obj->getView() == $view) {
                return $obj;
            }
        }

        return null;
    }

    public function get(): array
    {
        return $this->views;
    }
}
