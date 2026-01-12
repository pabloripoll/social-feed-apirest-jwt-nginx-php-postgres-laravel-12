<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModerationMessage extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserModerationMessage> */
    use HasFactory;

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
