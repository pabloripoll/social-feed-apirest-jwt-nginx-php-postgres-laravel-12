<?php

namespace App\Domain\Admin\Models;

use App\Domain\User\Models\User;
use App\Domain\Admin\Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admin extends Model
{
    /** @use HasFactory<\App\Domain\Admin\Models\AdminFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'region_id',
        'is_active',
        'is_banned',
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
        return AdminFactory::new();
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
        return $this->hasOne(AdminProfile::class, 'user_id', 'user_id');
    }

    public function accessLogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Domain\Admin\Models\AdminAccessLog::class,
            \App\Domain\User\Models\User::class,
            'id',        // Foreign key on users table...
            'user_id',   // Foreign key on member_access_logs table...
            'user_id',   // Local key on members table...
            'id'         // Local key on users table...
        );
    }
}
