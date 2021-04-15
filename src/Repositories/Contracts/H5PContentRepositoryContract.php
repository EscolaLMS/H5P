<?php
namespace EscolaLms\HeadlessH5P\Repositories\Contracts;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

interface H5PContentRepositoryContract
{
    public function create(string $title, string $library, string $params):H5PContent;
}
