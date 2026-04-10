<?php

namespace App\Modules\Feed\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property bool $up
 * @property bool $down
 * @property int $refresh_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb whereDown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb whereRefreshCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb whereUp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedPostThumb whereUserId($value)
 * @mixin \Eloquent
 */
class FeedPostThumb extends Model
{
    /** @use HasFactory<\App\Modules\Feed\Database\Factories\MemberProfileFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_posts_thumbs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'up',
        'down',
        'refresh_count',
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
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
