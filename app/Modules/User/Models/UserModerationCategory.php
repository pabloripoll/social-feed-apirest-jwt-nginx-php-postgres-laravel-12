<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property int $level
 * @property int $position
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\User\Models\UserModeration> $moderations
 * @property-read int|null $moderations_count
 * @method static \App\Modules\User\Database\Factories\UserModerationCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserModerationCategory extends Model
{
    /** @use HasFactory<\App\Modules\User\Database\Factories\UserModerationCategoryFactory> */
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
        return \App\Modules\User\Database\Factories\UserModerationCategoryFactory::new();
    }

    /**
     * Relations
     */
    public function moderations(): HasMany
    {
        return $this->hasMany(UserModeration::class, 'category_id');
    }
}
