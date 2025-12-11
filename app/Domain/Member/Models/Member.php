<?php

namespace App\Domain\Member\Models;

use App\Domain\Geo\Models\GeoContinent;
use App\Domain\Geo\Models\GeoRegion;
use App\Domain\Member\Database\Factories\MemberFactory;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    /** @use HasFactory<\App\Domain\Member\Models\MemberFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'continent_id',
        'region_id',
        'is_active',
        'is_banned',
        'feed_posts_count',
        'feed_posts_thumbs_up_count',
        'feed_posts_thumbs_down_count',
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
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return MemberFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(MemberProfile::class, 'user_id', 'user_id');
    }

    public function accessLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Domain\Member\Models\MemberAccessLog::class,
            \App\Domain\User\Models\User::class,
            'id',        // Foreign key on users table...
            'user_id',   // Foreign key on member_access_logs table...
            'user_id',   // Local key on members table...
            'id'         // Local key on users table...
        );
    }

    public function continent(): BelongsTo
    {
        return $this->belongsTo(GeoContinent::class, 'continent_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(GeoRegion::class, 'region_id', 'id');
    }
}
