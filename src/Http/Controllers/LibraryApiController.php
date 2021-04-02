<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\LibraryApiSwagger;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryStoreRequest;

class LibraryApiController extends Controller implements LibraryApiSwagger
{
    private HeadlessH5PService $hh5pService;

    public function __construct(HeadlessH5PService $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    public function store(LibraryStoreRequest $request)
    {
        $valid = $this->hh5pService->validatePackage($request->file('h5p_file'));
        if ($valid) {
            $this->hh5pService->savePackage();
        }

        return response()->json([
            'valid' => $valid,
            'messages' => $this->hh5pService->getMessages('updated'),
            'errors' => $this->hh5pService->getMessages('error'),
        ], $valid ? 200 : 400);
    }
}
