<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

//use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\EditorApiSwagger;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

use Illuminate\Routing\Controller;

class EditorApiController extends Controller implements EditorApiSwagger
{
    private HeadlessH5PServiceContract $hh5pService;

    public function __construct(HeadlessH5PServiceContract $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $settings = $this->hh5pService->getEditorSettings();
        return response()->json($settings, 200);
    }
}
