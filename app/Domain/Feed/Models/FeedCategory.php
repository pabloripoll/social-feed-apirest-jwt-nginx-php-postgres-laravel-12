<?php

namespace App\Domain\Feed\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'posts_favourites_count',
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
            'posts_favourites_count' => 'integer',
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
