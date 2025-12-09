<?php

namespace App\Domain\Feed\Models;

use App\Domain\Feed\Database\Factories\FeedMultimediaFactory;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedMultimedia extends Model
{
    /** @use HasFactory<\App\Domain\Feed\Database\Factories\FeedMultimedia> */
    // use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_multimedia';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'position',
        'type',
        'extension',
        'path',
        'name',
        'title',
        'url',
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
        ];
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return FeedMultimediaFactory::new();
    }

    /**
     * Relations
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'post_id');
    }
}
