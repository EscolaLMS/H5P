<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

//use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
//use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\ContentStoreRequest;
use EscolaLms\HeadlessH5P\Services\HeadlessH5PService;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

use EscolaLms\HeadlessH5P\Http\Requests\ContentStoreRequest;
use Illuminate\Routing\Controller;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;

class ContentApiController extends Controller /*implements LibraryApiSwagger*/
{
    private HeadlessH5PServiceContract $hh5pService;
    private H5PContentRepositoryContract $contentRepository;

    public function __construct(HeadlessH5PServiceContract $hh5pService, H5PContentRepositoryContract $contentRepository)
    {
        $this->hh5pService = $hh5pService;
        $this->contentRepository = $contentRepository;
    }

    /*
    public function index(Request $request): JsonResponse
    {
        $libraries = $this->hh5pService->listLibraries();

        return response()->json($libraries, 200);
    }
    */

    public function store(ContentStoreRequest $request): JsonResponse
    {
        $content = $this->contentRepository->create($request->get('title'), $request->get('library'), $request->get('params'));

        return response()->json([
            $content
        ], 200);
    }



    /*
    public function destroy(Request $request, int $id): JsonResponse
    {
        $valid = $this->hh5pService->deleteLibrary($id);

        return response()->json([
            'valid' => $valid,
            'messages' =>  $valid ? "Library $id deleted" : "",
            'errors' => !$valid ? "Library $id note deleted" : "",
        ], $valid ? 200 : 400);
    }
    */
}
