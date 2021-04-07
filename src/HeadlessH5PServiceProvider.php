<?php

namespace EscolaLms\HeadlessH5P;

use H5PCore;
use H5peditor;
use H5PEditorEndpoints;
use H5PFrameworkInterface;
use H5PFileStorage;
use H5PStorage;
use H5PValidator;
use EditorStorage;
use EditorAjaxRepository;
use H5peditorStorage;
use H5PEditorAjaxInterface;
use H5PContentValidator;

use Illuminate\Support\ServiceProvider;
use EscolaLms\HeadlessH5P\Commands\StorageH5PLinkCommand;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PFileStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorAjaxRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorStorageRepository;

class HeadlessH5PServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(H5PCore::class, function ($app) {
            return new HeadlessH5P();
        });
        $this->commands([StorageH5PLinkCommand::class]);
        $this->bindH5P();
    }

    private function bindH5P(): void
    {
        $this->app->bind(HeadlessH5PServiceContract::class, function ($app) {
            $repository = new H5pRepository();
            $fileStorage = new H5PFileStorageRepository(storage_path('app/h5p'));
            $core = new H5PCore($repository, $fileStorage, url('h5p'));
            $core->aggregateAssets = false;
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
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        //$this->loadFactoriesFrom(__DIR__ . '/../database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'h5p');
        // Load configs
    }
}
