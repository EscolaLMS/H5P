<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

//use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\ContentApiSwagger;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

use EscolaLms\HeadlessH5P\Http\Requests\ContentStoreRequest;
use Illuminate\Routing\Controller;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use Exception;

class ContentApiController extends Controller implements ContentApiSwagger
{
    private HeadlessH5PServiceContract $hh5pService;
    private H5PContentRepositoryContract $contentRepository;

    public function __construct(HeadlessH5PServiceContract $hh5pService, H5PContentRepositoryContract $contentRepository)
    {
        $this->hh5pService = $hh5pService;
        $this->contentRepository = $contentRepository;
    }
    
    public function index(Request $request): JsonResponse
    {
        $list = $this->contentRepository->list($request->get('per_page'));
        return response()->json($list, 200);
    }

    public function update(ContentStoreRequest $request, int $id): JsonResponse
    {
        try {
            $contentId = $this->contentRepository->edit($id, $request->get('title'), $request->get('library'), $request->get('params'), $request->get('nonce'));
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage()
            ], 422);
        }
    
        return response()->json([
            'id' => $contentId
        ], 200);
    }

    public function store(ContentStoreRequest $request): JsonResponse
    {
        try {
            $contentId = $this->contentRepository->create($request->get('title'), $request->get('library'), $request->get('params'), $request->get('nonce'));
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage()
            ], 422);
        }
    
        return response()->json([
            'id' => $contentId
        ], 200);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $contentId = $this->contentRepository->delete($id);
        } catch (Exception $error) {
            return response()->json([
            'error' => $error->getMessage()
        ], 422);
        }

        return response()->json([
            'id' => $contentId
        ], 200);
    }
}
