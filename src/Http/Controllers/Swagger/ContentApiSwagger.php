<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use EscolaLms\HeadlessH5P\Http\Requests\ContentStoreRequest;

    /**
     * @OA\Schema(
     *      schema="H5PContentStore",
     *      type="object",
     *      @OA\Property(
     *          property="title",
     *          description="Title of new content",
     *          type="string"
     *      ),
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
    *          response=400,
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
    public function store(ContentStoreRequest $request): JsonResponse;
}
