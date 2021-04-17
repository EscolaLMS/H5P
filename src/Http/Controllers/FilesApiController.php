<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

//use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\EditorApiSwagger;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

use Illuminate\Routing\Controller;
use Exception;

    // TODO add swagger

class FilesApiController extends Controller /*implements EditorApiSwagger*/
{
    private HeadlessH5PServiceContract $hh5pService;

    public function __construct(HeadlessH5PServiceContract $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    // TODO add bespoke Request
    public function __invoke(Request $request, int $id = null): JsonResponse
    {
        try {
            $result = $this->hh5pService->uploadFile($request->get('contentId'), $request->get('field'), $request->get('_token'));
            return response()->json($result);
        } catch (Exception $error) {
            return response()->json(['error'=>$error->getMessage()], 400);
        }
    }
}
