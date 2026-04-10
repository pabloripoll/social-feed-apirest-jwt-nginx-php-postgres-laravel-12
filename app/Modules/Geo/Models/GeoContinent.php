<?php

namespace App\Modules\Geo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Geo\Models\GeoRegion> $regions
 * @property-read int|null $regions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoContinent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoContinent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoContinent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoContinent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoContinent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoContinent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeoContinent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeoContinent extends Model
{
    /** @use HasFactory<\App\Modules\Member\Database\Factories\GeoContinent> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'geo_continents';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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
     * Relationships
     */
    public function regions(): HasMany
    {
        return $this->hasMany(GeoRegion::class, 'continent_id');
    }
}
