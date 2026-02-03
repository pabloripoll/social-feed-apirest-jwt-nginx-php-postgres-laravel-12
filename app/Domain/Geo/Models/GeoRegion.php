<?php

namespace App\Domain\Geo\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $continent_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Geo\Models\GeoContinent $continent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion whereContinentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoRegion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeoRegion extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\GeoRegion> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'geo_regions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'continent_id',
        'name',
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
     * Relations
     */
    public function continent(): BelongsTo
    {
        return $this->belongsTo(GeoContinent::class, 'continent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'region_id');
    }
}
