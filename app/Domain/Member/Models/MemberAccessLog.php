<?php

namespace App\Domain\Member\Models;

use App\Models\User;
use App\Domain\Member\Database\Factories\MemberAccessLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAccessLog extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\MemberAccessLog> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'members_access_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'is_expired',
        'expires_at',
        'refresh_count',
        'ip_address',
        'user_agent',
        'payload',
        'requests_count',
        'payload',
        'token',
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
            'payload' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Model factory when is outside ./database/factories
     */
    public static function newFactory()
    {
        return MemberAccessLogFactory::new();
    }

    /**
     * Relations
     */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
