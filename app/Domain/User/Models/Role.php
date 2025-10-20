<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ADMIN = 'admin';

    public const MEMBER = 'member';
}
