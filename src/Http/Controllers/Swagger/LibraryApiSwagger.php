<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use EscolaLms\HeadlessH5P\Http\Requests\LibraryStoreRequest;

interface LibraryApiSwagger
{
    /**
    * @param CreateAttachmentRequest $request
    * @return JsonResponse
    *
    * @OA\Post(
    *      path="/api/hh5p/library",
    *      summary="Store h5p library in database",
    *      tags={"Attachments"},
    *      description="Store Library",
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
    public function store(LibraryStoreRequest $request);
}
