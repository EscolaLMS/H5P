<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\Exceptions\H5PException;

class H5PContentRepository implements H5PContentRepositoryContract
{
    private HeadlessH5PServiceContract $hh5pService;

    public function __construct(HeadlessH5PServiceContract $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    public function create(string $title, string $library, string $params):int
    {
        $libNames = $this->hh5pService->getCore()->libraryFromString($library);

        $libDb = H5PLibrary::where([
            ['name', $libNames['machineName']],
            ['major_version', $libNames['majorVersion']],
            ['minor_version', $libNames['minorVersion']],
        ])->first();

        if ($libDb === null) {
            throw new H5PException(H5PException::LIBRARY_NOT_FOUND);
        }
        
        $json = json_decode($params);

        if ($json === null) {
            throw new H5PException(H5PException::INVALID_PARAMETERS_JSON);
        }

        return $this->hh5pService->getCore()->saveContent([
            'library_id'=> $libDb->id,
            'title'=>$title,
            'library'=>$library,
            'parameters'=>$params
        ]);
    }
}
