<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use EscolaLms\HeadlessH5P\Http\Requests\LibraryDeleteRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryListRequest;
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
    public function libraries(LibraryListRequest $request);
}
