<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @mixin \Eloquent
 */
class Role extends Model
{
    public const ADMIN = 'ROLE_ADMIN';

    public const MEMBER = 'ROLE_MEMBER';
}
