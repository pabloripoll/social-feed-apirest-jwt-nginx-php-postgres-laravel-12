<?php

namespace App\Domain\Feed\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedReport extends Model
{
    /** @use HasFactory<\App\Domain\Feed\Database\Factories\FeedReport> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'feed_reports';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type_id',
        'reporter_user_id',
        'reporter_message',
        'in_review',
        'in_review_since',
        'is_closed',
        'closed_at',
        'moderation_id',
        'member_user_id',
        'member_feed_post_id',
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
            'in_review_since' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
