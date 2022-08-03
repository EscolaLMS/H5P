<?php

namespace EscolaLms\HeadlessH5P;

use EscolaLms\HeadlessH5P\Commands\H5PSeedCommand;
use EscolaLms\HeadlessH5P\Commands\StorageH5PLinkCommand;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use EscolaLms\HeadlessH5P\Repositories\H5PContentRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorAjaxRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PFileStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use H5PContentValidator;
use H5PCore;
use H5peditor;
use H5PStorage;
use H5PValidator;
use Illuminate\Support\ServiceProvider;

/**
 * SWAGGER_VERSION.
 */
class HeadlessH5PServiceProvider extends ServiceProvider
{
    public $singletons = [
        H5PContentRepositoryContract::class => H5PContentRepository::class,
    ];

    public function register(): void
    {
        $this->commands([H5PSeedCommand::class, StorageH5PLinkCommand::class]);
        $this->bindH5P();
        $this->app->register(AuthServiceProvider::class);
    }

    private function bindH5P(): void
    {
        $this->app->singleton(HeadlessH5PServiceContract::class, function ($app) {
            $repository = new H5PRepository();
            $fileStorage = new H5PFileStorageRepository(storage_path('app/h5p'));
            $core = new H5PCore($repository, $fileStorage, url('h5p'), config('hh5p.language'), true);
            $core->aggregateAssets = true;
            $validator = new H5PValidator($repository, $core);
            $storage = new H5PStorage($repository, $core);
            $editorStorage = new H5PEditorStorageRepository();
            $editorAjaxRepository = new H5PEditorAjaxRepository();
            // TODO might be replaced with custom H5peditor
            $editor = new H5peditor($core, $editorStorage, $editorAjaxRepository);
            $contentValidator = new H5PContentValidator($repository, $core);

            return new HeadlessH5PService(
                $repository,
                $fileStorage,
                $core,
                $validator,
                $storage,
                $editorStorage,
                $editorAjaxRepository,
                $editor,
                $contentValidator
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        //$this->loadFactoriesFrom(__DIR__ . '/../database/factories');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'h5p');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');
        $this->mergeConfigFrom(__DIR__.'/../config/hh5p.php', 'hh5p');
        // Load configs
    }
}
