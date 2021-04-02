<?php
namespace EscolaLms\HeadlessH5P\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface HeadlessH5PServiceContract
{
    public function validatePackage(UploadedFile $file, bool $skipContent, bool $h5p_upgrade_only): bool;

    public function savePackage(object $content, int $contentMainId, bool $skipContent, array $options): bool;
}
