<?php
namespace EscolaLms\HeadlessH5P\Services\Contracts;

use Illuminate\Http\UploadedFile;

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
use EscolaLms\HeadlessH5P\Repositories\H5PFileStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorAjaxRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorStorageRepository;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

interface HeadlessH5PServiceContract
{
    public function getEditor():H5peditor;

    public function getRepository():H5PFrameworkInterface;
    
    public function getFileStorage():H5PFileStorage;
 
    public function getCore():H5PCore;

    public function getValidator():H5PValidator;

    public function getStorage():H5PStorage;

    public function getContentValidator():H5PContentValidator;

    public function validatePackage(UploadedFile $file, bool $skipContent, bool $h5p_upgrade_only): bool;

    public function savePackage(object $content, int $contentMainId, bool $skipContent, array $options): bool;

    public function getEditorSettings($content = null): array;
}
