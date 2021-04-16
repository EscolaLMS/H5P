<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use EscolaLms\HeadlessH5P\Http\Requests\ContentStoreRequest;

interface ContentApiSwagger
{

    /**
    * @OA\Post(
    *      path="/api/hh5p/content",
    *      summary="Store h5p content in database",
    *      tags={"H5P"},
    *      description="Store h5p content in database",
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
    public function store(ContentStoreRequest $request): JsonResponse;
}
