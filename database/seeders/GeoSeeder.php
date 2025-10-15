<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeoContinent;
use App\Models\GeoRegion;

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
