<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property int $position
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\User\Models\UserModeration> $moderations
 * @property-read int|null $moderations_count
 * @method static \App\Domain\User\Database\Factories\UserModerationSanctionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationSanction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
