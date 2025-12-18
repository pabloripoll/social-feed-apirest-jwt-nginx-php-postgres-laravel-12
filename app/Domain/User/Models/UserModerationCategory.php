<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModerationCategory extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserModerationCategoryFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users_moderation_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'level',
        'position',
        'title',
        'description',
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

    /**
     * Factory
     */
    public static function newFactory()
    {
        return \App\Domain\User\Database\Factories\UserModerationCategoryFactory::new();
    }
}
