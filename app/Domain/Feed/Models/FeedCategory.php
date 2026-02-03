<?php

namespace App\Domain\Feed\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property int $position
 * @property-read int|null $posts_count
 * @property int $posts_thumbs_up_count
 * @property int $posts_thumbs_down_count
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\Feed\Models\FeedPost> $posts
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory wherePostsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory wherePostsThumbsDownCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory wherePostsThumbsUpCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FeedCategory extends Model
{
    /** @use HasFactory<\App\Domain\Feed\Database\Factories\FeedCategoryFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'position',
        'posts_count',
        'posts_thumbs_up_count',
        'posts_thumbs_down_count',
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
        return [
            'posts_count' => 'integer',
            'posts_votes_up_count' => 'integer',
            'posts_votes_down_count' => 'integer',
        ];
    }

    /**
     * Relations
     */
    public function posts(): HasMany
    {
        return $this->hasMany(FeedPost::class, 'category_id', 'id');
    }
}
