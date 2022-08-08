<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use EscolaLms\HeadlessH5P\Http\Requests\LibraryDeleteRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryInstallRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryListRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use EscolaLms\HeadlessH5P\Http\Requests\LibraryStoreRequest;

interface LibraryApiSwagger
{

    /**
     * @OA\Post(
     *      path="/api/hh5p/library",
     *      summary="Store h5p library in database",
     *      tags={"H5P"},
     *      description="Store h5p library in database",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="h5p_file",
     *                      type="string",
     *                      format="binary"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     * )
     */
    public function store(LibraryStoreRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *      path="/api/hh5p/library/{id}",
     *      summary="Deletes h5p library from database",
     *      tags={"H5P"},
     *      description="Deletes h5p library from database",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of library that will be deleted",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *             type="integer",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     * )
     */
    public function destroy(LibraryDeleteRequest $request, int $id): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/hh5p/library",
     *      summary="Gets all h5p libraries in the database",
     *      tags={"H5P"},
     *      description="Gets all h5p libraries in the database",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/H5PLibrary")
     *         )
     *      )
     * )
     */
    public function index(LibraryListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/hh5p/libraries",
     *      summary="H5P Editor Endpoint. Gets all h5p runnable libraries in the database. Called by  ",
     *      tags={"H5P"},
     *      description="Gets all h5p runnable libraries in the database. Called by H5P Editor",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/H5PLibrary")
     *         )
     *      )
     * )
     */

    /**
     * @OA\Get(
     *      path="/api/hh5p/libraries?",
     *      summary="H5P Editor Endpoint. Get specific library object  ",
     *      tags={"H5P"},
     *      description="Get specific library object",
     *      @OA\Parameter(
     *          name="machineName",
     *          description="machine Name of Library",
     *          example="H5P.ArithmeticQuiz",
     *          in="query",
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="majorVersion",
     *          description="major Version",
     *          example="1",
     *          in="query",
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="minorVersion",
     *          description="minor Version",
     *          example="1",
     *          in="query",
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *             type="object",
     *             ref="#/components/schemas/H5PLibraryForEditor"
     *         )
     *      )
     * )
     */
    public function libraries(LibraryListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/hh5p/content-type-cache",
     *      summary="H5P Editor Endpoint. Gets content type cache for globally available libraries",
     *      tags={"H5P"},
     *      description="Gets content type cache for globally available libraries",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *      )
     * )
     */
    public function contentTypeCache(Request $request): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/hh5p/content-hub-metadata-cache",
     *      summary="Gets contents hub metadata cache",
     *      tags={"H5P"},
     *      description="Gets contents hub metadata cache",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *      )
     * )
     */
    public function contentHubMetadata(Request $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/hh5p/library-install",
     *      summary="Install H5P library by Machine name",
     *      tags={"H5P"},
     *      description="Install H5P library by Machine name",
     *      @OA\Parameter(
     *          name="id",
     *          description="Machine name of Library",
     *          example="H5P.ArithmeticQuiz",
     *          in="query",
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *      )
     * )
     */
    public function libraryInstall(LibraryInstallRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/hh5p/library-upload",
     *      summary="Upload H5P library",
     *      tags={"H5P"},
     *      description="Upload H5P library",
     *      @OA\Parameter(
     *          name="id",
     *          description="Machine name of Library",
     *          example="H5P.ArithmeticQuiz",
     *          in="query",
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="content_id",
     *          description="Content id",
     *          in="query",
     *          @OA\Schema(
     *             type="number",
     *         )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="h5p",
     *                      type="string",
     *                      format="binary"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *      )
     * )
     */
    public function libraryUpload(LibraryUploadRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/hh5p/filter",
     *      summary="Filter H5P libraries",
     *      tags={"H5P"},
     *      description="Filter H5P libraries",
     *      @OA\Parameter(
     *          name="libraryParameters",
     *          description="Library parameters",
     *          in="query",
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *      )
     * )
     */
    public function filter(Request $request): JsonResponse;


    /**
     * @OA\Post(
     *      path="/api/hh5p/translations",
     *      summary="Libraries translations",
     *      tags={"H5P"},
     *      description="Libraries translations",
     *      @OA\Parameter(
     *          name="language",
     *          description="Language",
     *          in="query",
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *      )
     * )
     */
    public function translations(Request $request): JsonResponse;
}
