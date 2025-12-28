<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationType extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserNotificationType> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users_notification_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'title_single',
        'title_multiple',
        'summary_single',
        'summary_multiple',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }
}
