<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $moderation_id
 * @property int $user_id
 * @property bool $is_viewed
 * @property \Illuminate\Support\Carbon|null $viewed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $content
 * @property-read \App\Domain\User\Models\UserModeration $moderation
 * @property-read \App\Domain\User\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereIsViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereModerationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserModerationMessage whereViewedAt($value)
 * @mixin \Eloquent
 */
class UserModerationMessage extends Model
{
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
        'moderation_id',
        'user_id',
        'content',
        'is_viewed',
        'viewed_at',
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
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'viewed_at' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function moderation(): BelongsTo
    {
        return $this->belongsTo(UserModeration::class, 'moderation_id');
    }
}
