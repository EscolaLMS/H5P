<?php

namespace EscolaLms\HeadlessH5P;

use Illuminate\Support\ServiceProvider;
use EscolaLms\HeadlessH5P\HeadlessH5P;

class HeadlessH5PServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(H5PCore::class, function ($app) {
            return new HeadlessH5P();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        //$this->loadFactoriesFrom(__DIR__ . '/../database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Load configs
    }
}
