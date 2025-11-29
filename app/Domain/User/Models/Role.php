<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ADMIN = 'ROLE_ADMIN';

    public const MEMBER = 'ROLE_MEMBER';
}
