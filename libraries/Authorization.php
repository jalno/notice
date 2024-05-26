<?php

namespace packages\notice;

use packages\userpanel\Authorization as UserPanelAuthorization;

class Authorization extends UserPanelAuthorization
{
    public static function is_accessed($permission, $prefix = 'notice')
    {
        return parent::is_accessed($permission, $prefix);
    }

    public static function haveOrFail($permission, $prefix = 'notice')
    {
        parent::haveOrFail($permission, $prefix);
    }
}
