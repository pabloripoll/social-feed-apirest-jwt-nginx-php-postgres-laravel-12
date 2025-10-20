<?php

namespace App\Domain\User;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
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
