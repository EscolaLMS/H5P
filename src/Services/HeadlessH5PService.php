<?php

namespace EscolaLms\HeadlessH5P\Services;

use Illuminate\Http\UploadedFile;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\HeadlessH5P;

class HeadlessH5PService implements HeadlessH5PServiceContract
{
    public function __construct(HeadlessH5P $h5p)
    {
        $this->h5p = $h5p;
    }

    /** Copy file to `getUploadedH5pPath` and validates its contents */
    public function validatePackage(UploadedFile $file, $skipContent = true, $h5p_upgrade_only = false): bool
    {
        rename($file->getPathName(), $this->h5p->getRepository()->getUploadedH5pPath());
        try {
            $isValid = $this->h5p->getValidator()->isValidPackage($skipContent, $h5p_upgrade_only);
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
            $this->h5p->getStorage()->savePackage($content, $contentMainId, $skipContent, $options);
        } catch (Exception $err) {
            return false;
        }
        return true;
    }

    public function getMessages($type = 'error')
    {
        return $this->h5p->getRepository()->getMessages($type);
    }
}
