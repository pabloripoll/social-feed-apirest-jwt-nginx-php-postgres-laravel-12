<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserModerationSanction extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserModerationSanctionFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users_moderation_sanctions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
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
        return \App\Domain\User\Database\Factories\UserModerationSanctionFactory::new();
    }

    /**
     * Relations
     */
    public function moderations(): HasMany
    {
        return $this->hasMany(UserModeration::class, 'sanction_id');
    }
}
