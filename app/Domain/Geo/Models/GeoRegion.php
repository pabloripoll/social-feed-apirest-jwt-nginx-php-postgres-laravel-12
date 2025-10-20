<?php

namespace App\Domain\Geo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domain\User\Models\User;

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
