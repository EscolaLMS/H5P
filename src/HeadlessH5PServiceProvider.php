<?php

namespace EscolaLms\HeadlessH5P;

use Illuminate\Support\ServiceProvider;
use EscolaLms\HeadlessH5P\HeadlessH5P;
use EscolaLms\HeadlessH5P\Commands\StorageH5PLinkCommand;

class HeadlessH5PServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(H5PCore::class, function ($app) {
            return new HeadlessH5P();
        });
        $this->commands([StorageH5PLinkCommand::class]);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        //$this->loadFactoriesFrom(__DIR__ . '/../database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'h5p');
        // Load configs
    }
}
