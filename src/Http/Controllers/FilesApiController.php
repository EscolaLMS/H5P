<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Exception;
use Illuminate\Http\JsonResponse;
use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\FilesApiSwagger;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\Http\Requests\FilesStoreRequest;

class FilesApiController extends EscolaLmsBaseController implements FilesApiSwagger
{
    private HeadlessH5PServiceContract $hh5pService;

    public function __construct(HeadlessH5PServiceContract $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    public function __invoke(FilesStoreRequest $request, String $nonce = null): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            abort(401);
        }
        try {
            $result = $this->hh5pService->uploadFile(
                $request->get('contentId'),
                $request->get('field'),
                $request->get('_token'),
                $nonce
            );

            return response()->json($result);
        } catch (Exception $error) {
            return response()->json(['error' => $error->getMessage()], 422);
        }
    }
}
