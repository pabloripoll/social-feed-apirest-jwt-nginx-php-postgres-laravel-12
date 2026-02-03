<?php

namespace App\Domain\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string|null $title_single
 * @property string|null $title_multiple
 * @property string|null $summary_single
 * @property string|null $summary_multiple
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereSummaryMultiple($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereSummarySingle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereTitleMultiple($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereTitleSingle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserNotificationType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserNotificationType extends Model
{
    /**
     * @var string
     */
    protected $table = 'users_notification_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'title_single',
        'title_multiple',
        'summary_single',
        'summary_multiple',
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
}
