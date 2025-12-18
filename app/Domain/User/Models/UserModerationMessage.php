<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModerationMessage extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserModerationMessage> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users_moderation_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'title',
        'description',
        'position',
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
