<?php

namespace App\Domain\Member\Models;

use App\Domain\Member\Database\Factories\MemberProfileFactory;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $nickname
 * @property string|null $name
 * @property int $age
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \App\Domain\Member\Database\Factories\MemberProfileFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberProfile whereUserId($value)
 * @mixin \Eloquent
 */
class MemberProfile extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\MemberProfileFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members_profile';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nickname',
        'name',
        'age',
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
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return MemberProfileFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
