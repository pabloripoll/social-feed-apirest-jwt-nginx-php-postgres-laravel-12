<?php

namespace App\Domain\Member\Models;

use App\Models\User;
use App\Domain\Member\Database\Factories\MemberActivationCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberActivationCode extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\MemberFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members_activation_code';

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
                $code = random_int(100000000, 999999999); // 9 digits
            } while (self::where('code', $code)->exists());

            $model->code = $code;
        });
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return MemberActivationCodeFactory::new();
    }

    /**
     * Relations
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
