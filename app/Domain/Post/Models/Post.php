<?php

namespace App\Domain\Post\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /** @use HasFactory<\App\Domain\Post\Database\Factories\PostFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'posts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'region_id',
        'uid',
        'is_active',
        'is_banned',
        'posts_count',
        'votes_count',
        'comments_count',
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
     * On creating register auto-generated values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a unique 9-digit integer UID
            do {
                $uid = random_int(100000, 999999); // 6 digits
            } while (self::where('uid', $uid)->exists());

            $model->uid = $uid;
        });
    }

    /**
     * Relations
     */

    /* public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    } */
}
