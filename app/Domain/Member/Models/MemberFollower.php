<?php

namespace App\Domain\Member\Models;

use App\Domain\Member\Database\Factories\MemberFollowerFactory;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $following_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Member\Models\Member $member
 * @property-read User $user
 * @method static \App\Domain\Member\Database\Factories\MemberFollowerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower whereFollowingUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MemberFollower whereUserId($value)
 * @mixin \Eloquent
 */
class MemberFollower extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\MemberFollowerFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members_followers';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'following_user_id',
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
        return MemberFollowerFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }
}
