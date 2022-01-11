<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

//use App\Http\Controllers\Controller;
use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\ContentApiSwagger;
use EscolaLms\HeadlessH5P\Http\Requests\ContentDeleteRequest;
use EscolaLms\HeadlessH5P\Http\Requests\ContentListRequest;
use EscolaLms\HeadlessH5P\Http\Requests\ContentReadRequest;
use EscolaLms\HeadlessH5P\Http\Requests\ContentStoreRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryStoreRequest;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ContentApiController extends Controller implements ContentApiSwagger
{
    private HeadlessH5PServiceContract $hh5pService;
    private H5PContentRepositoryContract $contentRepository;

    public function __construct(HeadlessH5PServiceContract $hh5pService, H5PContentRepositoryContract $contentRepository)
    {
        $this->hh5pService = $hh5pService;
        $this->contentRepository = $contentRepository;
    }

    public function index(ContentListRequest $request): JsonResponse
    {
        $columns = ['title', 'id', 'library_id'];
        $list = $request->get('per_page') !== null && $request->get('per_page') == 0 ? $this->contentRepository->unpaginatedList($columns) : $this->contentRepository->list($request->get('per_page'), $columns);

        return response()->json($list, 200);
    }

    public function update(ContentStoreRequest $request, int $id): JsonResponse
    {
        try {
            $contentId = $this->contentRepository->edit($id, $request->get('title'), $request->get('library'), $request->get('params'), $request->get('nonce'));
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => $contentId,
        ], 200);
    }

    public function store(ContentStoreRequest $request): JsonResponse
    {
        try {
            $contentId = $this->contentRepository->create($request->get('title'), $request->get('library'), $request->get('params'), $request->get('nonce'));
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => $contentId,
        ], 200);
    }

    public function destroy(ContentDeleteRequest $request, int $id): JsonResponse
    {
        try {
            $contentId = $this->contentRepository->delete($id);
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        }

        return response()->json([
            'id' => $contentId,
        ], 200);
    }

    public function show(ContentReadRequest $request, int $id): JsonResponse
    {
        try {
            $settings = $this->hh5pService->getContentSettings($id);
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        }

        return response()->json(
            $settings,
            200
        );
    }

    public function upload(LibraryStoreRequest $request): JsonResponse
    {
        try {
            $content = $this->contentRepository->upload($request->file('h5p_file'));
        } catch (Exception $error) {
            return response()->json([
                'error' => $error->getMessage(),
            ], 422);
        }

        return response()->json(
            $content,
            200
        );
    }

    public function download(ContentReadRequest $request, $id)
    {
        $filepath = $this->contentRepository->download($id);

        return response()
            ->download($filepath, '', [
                'Content-Type' => 'application/zip',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            ]);
    }
}
