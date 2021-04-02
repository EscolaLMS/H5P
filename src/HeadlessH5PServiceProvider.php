<?php

namespace EscolaLms\HeadlessH5P;

use EscolaLms\HeadlessH5P\Repositories\H5pDefaultFileStorage;
use EscolaLms\HeadlessH5P\Repositories\H5pRepository;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PContract;
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
        $this->bindH5P();
    }

    private function bindH5P(): void
    {
        $this->app->bind(HeadlessH5PContract::class, function ($app) {
            $repository = new H5pRepository();
            $fileStorage = new H5pDefaultFileStorage(storage_path('app/h5p'));
            $core = new \H5PCore($this->repository, $this->fileStorage, url(''));
            $validator = new \H5PValidator($this->repository, $this->core);
            $storage = new \H5PStorage($this->repository, $this->core);

            return new HeadlessH5P($repository, $fileStorage, $core, $validator, $storage);
        });

    }
}
