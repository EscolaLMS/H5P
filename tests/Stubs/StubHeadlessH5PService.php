<?php

namespace EscolaLms\HeadlessH5P\Tests\Stubs;

use EscolaLms\HeadlessH5P\Repositories\H5PEditorAjaxRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PFileStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use H5PContentValidator;
use H5peditor;
use H5PStorage;
use H5PValidator;

class StubHeadlessH5PService
{
    public static function instance(): callable
    {
        return function () {
            $repository = new H5PRepository();
            $fileStorage = new H5PFileStorageRepository(storage_path('app/h5p'));
            $core = new StubH5PCore($repository, $fileStorage, url('h5p'), config('hh5p.language'), true);
            $validator = new H5PValidator($repository, $core);
            $storage = new H5PStorage($repository, $core);
            $editorStorage = new H5PEditorStorageRepository();
            $editorAjaxRepository = new H5PEditorAjaxRepository();
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
        };
    }
}
