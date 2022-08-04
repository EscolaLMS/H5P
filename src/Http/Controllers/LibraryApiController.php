<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryDeleteRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryInstallRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryListRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryUploadRequest;
use EscolaLms\HeadlessH5P\Http\Resources\LibraryResource;
use Illuminate\Http\JsonResponse;
use EscolaLms\HeadlessH5P\Http\Controllers\Swagger\LibraryApiSwagger;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;

use EscolaLms\HeadlessH5P\Http\Requests\LibraryStoreRequest;
use Illuminate\Http\Request;

class LibraryApiController extends EscolaLmsBaseController implements LibraryApiSwagger
{
    private HeadlessH5PServiceContract $hh5pService;

    public function __construct(HeadlessH5PServiceContract $hh5pService)
    {
        $this->hh5pService = $hh5pService;
    }

    public function index(LibraryListRequest $request): JsonResponse
    {
        $libraries = $this->hh5pService->listLibraries();

        return $this->sendResponseForResource(LibraryResource::collection($libraries));
    }

    public function store(LibraryStoreRequest $request): JsonResponse
    {
        $valid = $this->hh5pService->validatePackage($request->file('h5p_file'));
        if ($valid) {
            $this->hh5pService->savePackage();

            return $this->sendResponse($this->hh5pService->getMessages('updated'));
        }

        return $this->sendError($this->hh5pService->getMessages('error'), 422);
    }

    public function libraries(LibraryListRequest $request): JsonResponse
    {
        $libraries = $this->hh5pService->getLibraries(
            $request->get('machineName'),
            $request->get('majorVersion'),
            $request->get('minorVersion')
        );

        return response()->json($libraries);
    }

    public function destroy(LibraryDeleteRequest $request, int $id): JsonResponse
    {
        $valid = $this->hh5pService->deleteLibrary($id);

        if ($valid) {
            return $this->sendSuccess("Library $id deleted");
        }

        return $this->sendError("Library $id note deleted", 422);
    }

    public function contentTypeCache(Request $request): JsonResponse
    {
        $contentTypeCache = $this->hh5pService->getContentTypeCache();

        return response()->json($contentTypeCache);
    }

    public function contentHubMetadata(Request $request): JsonResponse
    {
        $metadata = $this->hh5pService->getUpdatedContentHubMetadataCache();

        return response()->json(['success' => true, 'data' => $metadata]);
    }

    public function libraryInstall(LibraryInstallRequest $request): JsonResponse
    {
        // TODO here is token sent somehow. validate this
        $library = $this->hh5pService->libraryInstall($request->getMachineName());
        return response()->json(['success' => true, 'data' => $library]);
    }

    public function libraryUpload(LibraryUploadRequest $request): JsonResponse
    {
        $library = $this->hh5pService->uploadLibrary($request->getId(), $request->getH5PFile(), $request->getContentId());

        return response()->json(['success' => true, 'data' => $library]);
    }

    public function filter(Request $request): JsonResponse
    {
        $libraryParameters = $request->get('libraryParameters');
        $libraries = $this->hh5pService->filterLibraries($libraryParameters);

        return response()->json(['success' => true, 'data' => $libraries]);
    }

    public function translations(Request $request): JsonResponse
    {
        $libraries = $request->get('libraries');
        $language = $request->get('language');

        $translations = $this->hh5pService->getTranslations($libraries, $language);

        return response()->json(['success' => true, 'data' => $translations]);
    }
}
