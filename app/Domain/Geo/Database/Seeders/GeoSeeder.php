<?php

namespace App\Domain\Geo\Database\Seeders;

use App\Domain\Geo\Models\GeoContinent;
use App\Domain\Geo\Models\GeoRegion;
use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    /**
     * Using a Model by the United Nations (UN Geoscheme)
     * The United Nations geoscheme divides continents into further subgroups:
     */
    protected function data()
    {
        return [
            'Europe' => [
                'Eastern', 'Northern', 'Southern', 'Western',
            ],
            'Africa' => [
                'Eastern', 'Middle', 'Northern', 'Southern', 'Western',
            ],
            'Americas' => [
                'Caribbean', 'Central', 'Northern', 'South',
            ],
            'Asia' => [
                'Central', 'Eastern', 'South-Eastern', 'Southern', 'Western',
            ],
            'Oceania' => [
                'Australia and New Zealand', 'Melanesia', 'Micronesia', 'Polynesia',
            ],
        ];
    }

    /**
     * $ php artisan db:seed --class=GeoSeeder
     */
    public function run(): void
    {
        foreach ($this->data() as $continentName => $regions) {
            $continent = GeoContinent::firstOrCreate(['name' => $continentName]);
            foreach ($regions as $regionName) {
                GeoRegion::firstOrCreate([
                    'continent_id' => $continent->id,
                    'name' => $regionName,
                ]);
            }
        }
    }
}
