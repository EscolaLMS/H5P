<?php

namespace EscolaLms\HeadlessH5P;

use H5PFrameworkInterface;
use H5PFileStorage;
use H5PStorage;
use H5PCore;
use H5PValidator;
use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Repositories\H5pDefaultFileStorage;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

class HeadlessH5P
{
    private H5PFrameworkInterface $repository;
    private H5PFileStorage $fileStorage;
    private H5PCore $core;
    private H5PValidator $validator;
    private H5PStorage $storage;

    public function __construct()
    {
        $this->repository = new H5pRepository();
        $this->fileStorage = new H5pDefaultFileStorage(storage_path('app/h5p'));
        $this->core = new H5PCore($this->repository, $this->fileStorage, url(''));
        $this->validator = new H5PValidator($this->repository, $this->core);
        $this->storage = new H5PStorage($this->repository, $this->core);
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
}
