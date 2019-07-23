<?php

namespace App\Models\NeoAuth;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $connection = 'neo';

    protected $table = 'user';

    protected $primaryKey = 'user_id';

    protected $hidden = [
        'password', 'remember_token', 'role_id'
    ];
}
