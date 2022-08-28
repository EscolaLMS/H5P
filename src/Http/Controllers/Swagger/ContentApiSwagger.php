<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use EscolaLms\HeadlessH5P\Http\Requests\ContentCreateRequest;
use EscolaLms\HeadlessH5P\Http\Requests\ContentDeleteRequest;
use EscolaLms\HeadlessH5P\Http\Requests\ContentListRequest;
use EscolaLms\HeadlessH5P\Http\Requests\AdminContentReadRequest;
use EscolaLms\HeadlessH5P\Http\Requests\ContentReadRequest;
use Illuminate\Http\JsonResponse;

use EscolaLms\HeadlessH5P\Http\Requests\ContentUpdateRequest;
use EscolaLms\HeadlessH5P\Http\Requests\LibraryStoreRequest;

    /**
     * @OA\Schema(
     *      schema="H5PContentStore",
     *      type="object",
     *      @OA\Property(
     *          property="library",
     *          description="ubername of library",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="params",
     *          description="params taken from editor",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="nonce",
     *          description="nonce taken from editor settings (random for new, hash for exisiting)",
     *          type="string"
     *      ),
     * )
    */

    /**
     *
     * @OA\Schema(
     *      schema="H5PContentList",
     *      type="object",
     *      @OA\Property(
     *          property="current_page",
     *          type="integer"
     *      ),
     *      @OA\Property(
     *          property="first_page_url",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="last_page",
     *          type="integer"
     *      ),
     *      @OA\Property(
     *          property="last_page_url",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="next_page_url",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="path",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="per_page",
     *          type="integer"
     *      ),
     *      @OA\Property(
     *          property="prev_page_url",
     *          type="string"
     *      ),
     *      @OA\Property(
     *          property="to",
     *          type="integer"
     *      ),
     *      @OA\Property(
     *          property="total",
     *          type="integer"
     *      ),
     *      @OA\Property(
     *          property="data",
     *          type="array",
     *          @OA\Items(ref="#/components/schemas/H5PContent")
     *      ),
     * )
     */



interface ContentApiSwagger
{
    /**
    * @OA\Post(
    *      path="/api/hh5p/content",
    *      summary="Store h5p content in database",
    *      tags={"H5P"},
    *      description="Store h5p content in database",
    *      security={
    *          {"passport": {}},
    *      },
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/H5PContentStore")
    *          )
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="successful operation",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      ),
    *      @OA\Response(
    *          response=422,
    *          description="validation error",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      ),
    *      @OA\Response(
    *          response=401,
    *          description="unauthorised",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      )
    * )
    */
    public function store(ContentCreateRequest $request): JsonResponse;

    /**
    * @OA\Get(
    *      path="/api/admin/hh5p/content/{id}",
    *      summary="Content settings for player",
    *      tags={"H5P"},
    *      description="Content settings for player",
    *      security={
    *          {"passport": {}},
    *      },
    *      @OA\Parameter(
    *          name="id",
    *          description="Id of Content",
    *          in="path",
    *          required=true,
    *          @OA\Schema(
    *             type="integer",
    *         )
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="successful operation",
    *          @OA\JsonContent(
    *             type="object",
    *             ref="#/components/schemas/H5PEditorSettings"
    *         )
    *      )
    * )
    */
    public function show(AdminContentReadRequest $request, int $id): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/hh5p/content/{uuid}",
     *      summary="Content settings for player",
     *      tags={"H5P"},
     *      description="Content settings for player",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="uuid",
     *          description="Uuid of Content",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *             type="object",
     *             ref="#/components/schemas/H5PEditorSettings"
     *         )
     *      )
     * )
     */
    public function frontShow(ContentReadRequest $request, string $uuid): JsonResponse;

    /**
    * @OA\Post(
    *      path="/api/hh5p/content/{id}",
    *      summary="Updates h5p content in database",
    *      tags={"H5P"},
    *      description="Updates h5p content in database",
    *      security={
    *          {"passport": {}},
    *      },
    *      @OA\Parameter(
    *          name="id",
    *          description="Id of Content from DB",
    *          in="path",
    *          required=true,
    *          @OA\Schema(
    *             type="integer",
    *         )
    *      ),
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(ref="#/components/schemas/H5PContentStore")
    *          )
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="successful operation",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      ),
    *      @OA\Response(
    *          response=422,
    *          description="validation error",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      ),
    *      @OA\Response(
    *          response=401,
    *          description="unauthorised",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      )
    * )
    */
    public function update(ContentUpdateRequest $request, int $id): JsonResponse;

    /**
    * @OA\Get(
    *      path="/api/hh5p/content",
    *      summary="list of h5ps content in database",
    *      tags={"H5P"},
    *      description="list of h5ps content in database",
    *      security={
    *          {"passport": {}},
    *      },
    *      @OA\Parameter(
    *          name="page",
    *          description="page of pagination",
    *          in="query",
    *          required=false,
    *          @OA\Schema(
    *             type="integer",
    *         )
    *      ),
    *      @OA\Parameter(
    *          name="per_page",
    *          description="items per page. If set to 0, returns ALL contents ",
    *          in="query",
    *          required=false,
    *          @OA\Schema(
    *             type="integer",
    *         )
    *      ),
    *     @OA\Parameter(
    *          name="title",
    *          description="search items by title",
    *          in="query",
    *          required=false,
    *          @OA\Schema(
    *             type="string",
    *         )
    *      ),
    *     @OA\Parameter(
    *          name="library_id",
    *          description="search items by library_id",
    *          in="query",
    *          required=false,
    *          @OA\Schema(
    *             type="integer",
    *         )
    *      ),
    *      @OA\Response(
    *          response=200,
    *          description="successful operation",
    *          @OA\JsonContent(
    *             type="object",
    *             ref="#/components/schemas/H5PContentList"
    *         )
    *      ),
    *      @OA\Response(
    *          response=422,
    *          description="validation error",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      ),
    *      @OA\Response(
    *          response=401,
    *          description="unauthorised",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      )
    * )
    */
    public function index(ContentListRequest $request): JsonResponse;

    /**
    * @OA\Delete(
    *      path="/api/hh5p/content/{id}",
    *      summary="Deletes h5p content from database",
    *      tags={"H5P"},
    *      description="Deletes h5p content from database",
    *      security={
    *          {"passport": {}},
    *      },
    *      @OA\Parameter(
    *          name="id",
    *          description="ID of content that will be deleted",
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
    *      ),
    *      @OA\Response(
    *          response=422,
    *          description="validation error",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      ),
    *      @OA\Response(
    *          response=401,
    *          description="unauthorised",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      )
    * )
    */
    public function destroy(ContentDeleteRequest $request, int $id): JsonResponse;

    /**
    * @OA\Post(
    *      path="/api/hh5p/content/upload",
    *      summary="Store h5p content from file in database",
    *      tags={"H5P"},
    *      description="Store h5p content from file in database",
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
    public function upload(LibraryStoreRequest $request): JsonResponse;

    /**
    * @OA\Get(
    *      path="/api/hh5p/content/{id}/export",
    *      summary="Downloads h5p content to packagee",
    *      tags={"H5P"},
    *      description="Downloads h5p content to package",
    *      security={
    *          {"passport": {}},
    *      },
    *      @OA\Parameter(
    *          name="id",
    *          description="ID of content that will be exported",
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
    *      ),
    *      @OA\Response(
    *          response=422,
    *          description="validation error",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      ),
    *      @OA\Response(
    *          response=401,
    *          description="unauthorised",
    *          @OA\MediaType(
    *              mediaType="application/json"
    *          )
    *      )
    * )
    */
    public function download(AdminContentReadRequest $request, int $id);

    /**
     * @OA\Delete(
     *      path="/api/hh5p/unused",
     *      summary="Deletes all unused h5p",
     *      tags={"H5P"},
     *      description="Deletes all unused h5p",
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="validation error",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthorised",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     * )
     */
    public function deleteUnused(): JsonResponse;
}
