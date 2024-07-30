<?php

namespace packages\notice;

use packages\base\DB;
use packages\base\DB\DBObject;
use packages\userpanel\User;
use packages\userpanel\UserType;
use packages\notice\Note\Param;

class Note extends DBObject
{
    use Paramable;
    public const active = 1;
    public const deactive = 2;
    public const alert = 'alert';
    public const modal = 'modal';
    protected $dbTable = 'notice_notes';
    protected $primaryKey = 'id';
    protected $dbFields = [
        'view' => ['type' => 'text', 'required' => true],
        'type' => ['type' => 'text', 'required' => true],
        'content' => ['type' => 'text', 'required' => true],
        'create_at' => ['type' => 'int'],
        'start_time' => ['type' => 'int'],
        'end_time' => ['type' => 'int'],
        'expire_at' => ['type' => 'int'],
        'title' => ['type' => 'text', 'required' => true],
        'status' => ['type' => 'int', 'required' => true],
    ];
    protected $relations = [
        'params' => ['hasMany', Param::class, 'note'],
    ];

    public function addUser(User $user)
    {
        DB::insert('notice_notes_users', [
            'user' => $user->id,
            'note' => $this->id,
        ]);
    }

    public function addUserType(UserType $usertype)
    {
        DB::insert('notice_notes_usertypes', [
            'type' => $usertype->id,
            'note' => $this->id,
        ]);
    }

    public function isClosed(User $user)
    {
        DB::where('user', $user->id);
        DB::where('note', $this->id);

        return DB::getValue('notice_notes_users', 'closed');
    }

    public function close(User $user)
    {
        DB::where('user', $user->id);
        DB::where('note', $this->id);
        if (DB::has('notice_notes_users')) {
            DB::where('user', $user->id);
            DB::where('note', $this->id);
            DB::update('notice_notes_users', [
                'closed' => true,
            ]);
        } else {
            DB::insert('notice_notes_users', [
                'user' => $user->id,
                'note' => $this->id,
                'closed' => true,
            ]);
        }
    }

    public function getUsers()
    {
        DB::where('note', $this->id);

        return DB::get('notice_notes_users', null, 'user');
    }

    public function getUserTypes()
    {
        DB::where('note', $this->id);

        return DB::get('notice_notes_usertypes', null, 'type');
    }

    public function deleteUser(?User $user = null)
    {
        DB::where('note', $this->id);
        if ($user) {
            DB::where('user', $user->id);
        }
        DB::delete('notice_notes_users');
    }

    public function deleteUserType(?UserType $type = null)
    {
        DB::where('note', $this->id);
        if ($type) {
            DB::where('type', $type->id);
        }
        DB::delete('notice_notes_usertypes');
    }
}
