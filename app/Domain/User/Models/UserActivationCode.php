<?php

namespace App\Domain\User\Models;

use App\Domain\User\Database\Factories\UserActivationCodeFactory;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserActivationCode extends Model
{
    /** @use HasFactory<\App\Domain\User\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users_activation_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'user_id',
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * On creating register auto-generated values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a unique 9-digit integer code
            do {
                // $code = random_int(100000000, 999999999); // 9 digits
                $code = Str::random(9); // 9 alphanumeric
            } while (self::where('code', $code)->exists());

            $model->code = $code;
        });
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return UserActivationCodeFactory::new();
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
