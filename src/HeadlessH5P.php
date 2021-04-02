<?php

namespace EscolaLms\HeadlessH5P;

use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PContract;
use H5PFrameworkInterface;
use H5PFileStorage;
use H5PStorage;
use H5PCore;
use H5PValidator;
use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Repositories\H5pDefaultFileStorage;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

class HeadlessH5P implements HeadlessH5PContract
{
    private H5PFrameworkInterface $repository;
    private H5PFileStorage $fileStorage;
    private H5PCore $core;
    private H5PValidator $validator;
    private H5PStorage $storage;

    /**
     * HeadlessH5P constructor.
     * @param H5PFrameworkInterface $repository
     * @param H5PFileStorage $fileStorage
     * @param H5PCore $core
     * @param H5PValidator $validator
     * @param H5PStorage $storage
     */
    public function __construct(H5PFrameworkInterface $repository, H5PFileStorage $fileStorage, H5PCore $core, H5PValidator $validator, H5PStorage $storage)
    {
        $this->repository = $repository;
        $this->fileStorage = $fileStorage;
        $this->core = $core;
        $this->validator = $validator;
        $this->storage = $storage;
    }

    public function getRepository(): H5PFrameworkInterface
    {
        return $this->repository;
    }

    public function getFileStorage(): H5PFileStorage
    {
        return $this->fileStorage;
    }

    public function getCore(): H5PCore
    {
        return $this->core;
    }

    public function getValidator(): H5PValidator
    {
        return $this->validator;
    }

    public function getStorage(): H5PStorage
    {
        return $this->storage;
    }
}
