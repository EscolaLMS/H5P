<?php

namespace EscolaLms\HeadlessH5P;

use Illuminate\Support\ServiceProvider;

class HeadlessH5PServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->injectContract(self::CONTRACTS);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        //$this->loadFactoriesFrom(__DIR__ . '/../database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        //$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'mad');
    }
}
