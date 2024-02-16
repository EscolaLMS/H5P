<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use EscolaLms\HeadlessH5P\Http\Requests\FilesStoreRequest;

interface FilesApiSwagger
{
    /**
     * @OA\Schema(
     *      schema="H5PContentFile",
     *      type="object",
     *                  @OA\Property(
     *                      property="file",
     *                      description="file uploaded by h5peditor",
     *                      type="file",
     *                      format="binary"
     *                  ),
     *                  @OA\Property(
     *                      property="field",
     *                      description="json description of file",
     *                      type="string",
     *                      format="string"
     *                  ),
     *                  @OA\Property(
     *                      property="contentId",
     *                      description="id of content",
     *                      type="string",
     *                      format="string"
     *                  )
     * )
     *
     *
     * @OA\Post(
     *      path="/api/hh5p/files",
     *      summary="Store h5p files for h5p editor",
     *      tags={"H5P"},
     *      description="Store h5p files for h5p editor",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/H5PContentFile")
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
     *
     *
     * @OA\Post(
     *      path="/api/hh5p/files/{nonce}",
     *      summary="Store h5p files for h5p editor",
     *      tags={"H5P"},
     *      description="Store h5p files for h5p editor",
     *      @OA\Parameter(
     *          name="nonce",
     *          description="nonce of current editing file",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *             type="string",
     *         )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/H5PContentFile")
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

    public function __invoke(FilesStoreRequest $request, string $nonce = null): JsonResponse;
}
