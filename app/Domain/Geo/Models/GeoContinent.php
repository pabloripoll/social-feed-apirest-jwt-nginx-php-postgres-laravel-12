<?php

namespace App\Domain\Geo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoContinent extends Model
{
    /** @use HasFactory<\App\Domain\Member\Database\Factories\GeoContinent> */
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
