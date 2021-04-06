<?php

namespace EscolaLms\HeadlessH5P;

use H5PFrameworkInterface;
use H5PFileStorage;
use H5PStorage;
use H5PCore;
use H5PValidator;
use H5peditor;
use EditorStorage;
use EditorAjaxRepository;
use H5peditorStorage;
use H5PEditorAjaxInterface;
use H5PContentValidator;


use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Repositories\H5pFileStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorAjaxRepository;
use EscolaLms\HeadlessH5P\Repositories\H5pEditorStorageRepository;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

class HeadlessH5P
{
    private H5PFrameworkInterface $repository;
    private H5PFileStorage $fileStorage;
    private H5PCore $core;
    private H5PValidator $validator;
    private H5PStorage $storage;
    private H5peditorStorage $editorStorage;
    private H5PEditorAjaxInterface $editorAjaxRepository;
    private H5PContentValidator $contentValidator;

    public function __construct()
    {

        // TODO config

        $this->repository = new H5pRepository();
        $this->fileStorage = new H5pFileStorageRepository(storage_path('app/h5p'));
        $this->core = new H5PCore($this->repository, $this->fileStorage, url('h5p'));
        $this->core->aggregateAssets = false;
        $this->validator = new H5PValidator($this->repository, $this->core);
        $this->storage = new H5PStorage($this->repository, $this->core);
        $this->editorStorage = new H5pEditorStorageRepository();
        $this->editorAjaxRepository = new H5PEditorAjaxRepository();
        // TODO might be replaced with custom H5peditor
        $this->editor = new H5peditor($this->core, $this->editorStorage, $this->editorAjaxRepository);
        $this->contentValidator = new H5PContentValidator($this->repository, $this->core);
    }

    public function getEditor():H5peditor
    {
        return $this->editor;
    }

    public function getRepository():H5PFrameworkInterface
    {
        return $this->repository;
    }

    public function getFileStorage():H5PFileStorage
    {
        return $this->fileStorage;
    }
    
    public function getCore():H5PCore
    {
        return $this->core;
    }
    
    public function getValidator():H5PValidator
    {
        return $this->validator;
    }

    public function getStorage():H5PStorage
    {
        return $this->storage;
    }

    public function getContentValidator():H5PContentValidator
    {
        return $this->contentValidator;
    }
}
