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
        $contentId = $request->get('contentId');
        $field = $request->get('field');
        $token = $request->get('_token');
        foreach (array_keys($request->all()) as $key) {
            if (!in_array($key, ['expires', 'signature'])) {
                $request->request->remove($key);
            }
        }
        if (!$request->hasValidSignature(false)) {
            abort(401);
        }
        try {
            $result = $this->hh5pService->uploadFile(
                $contentId,
                $field,
                $token,
                $nonce
            );

            return response()->json($result);
        } catch (Exception $error) {
            return response()->json(['error' => $error->getMessage()], 422);
        }
    }
}
