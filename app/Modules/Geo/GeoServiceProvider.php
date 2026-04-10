<?php

namespace App\Modules\Geo;

use Illuminate\Support\ServiceProvider;

class GeoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        /** Migrations */
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations/');

        /** Routes */
        $this->loadRoutesFrom(__DIR__.'/Routes/routes.php');
    }
}
