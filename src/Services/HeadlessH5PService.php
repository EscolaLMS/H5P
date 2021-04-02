<?php

namespace EscolaLms\HeadlessH5P\Services;

use Illuminate\Support\ServiceProvider;

use H5PFrameworkInterface;
use H5PFileStorage;
use H5PStorage;
use H5PCore;
use H5PValidator;
use Illuminate\Http\UploadedFile;


use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Repositories\H5pDefaultFileStorage;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

class HeadlessH5PService implements HeadlessH5PServiceContract
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

    /** Copy file to `getUploadedH5pPath` and validates its contents */
    public function validatePackage(UploadedFile $file, $skipContent = true, $h5p_upgrade_only = false): bool
    {
        rename($file->getPathName(), $this->repository->getUploadedH5pPath());
        try {
            $isValid = $this->validator->isValidPackage($skipContent, $h5p_upgrade_only);
        } catch (Exception $err) {
            var_dump($err);
        }
        return $isValid;
    }

    /**
   * Saves a H5P file
   *
   * @param null $content
   * @param int $contentMainId
   *  The main id for the content we are saving. This is used if the framework
   *  we're integrating with uses content id's and version id's
   * @param bool $skipContent
   * @param array $options
   * @return bool TRUE if one or more libraries were updated
   * TRUE if one or more libraries were updated
   * FALSE otherwise
   */
    public function savePackage(object $content = null, int $contentMainId = null, bool $skipContent = true, array $options = []): bool
    { // this is crazy, it does save package from `getUploadedH5pPath` path
        try {
            $this->storage->savePackage($content, $contentMainId, $skipContent, $options);
        } catch (Exception $err) {
            return false;
        }
        return true;
    }

    public function getMessages($type = 'error')
    {
        return $this->repository->getMessages($type);
    }
}
