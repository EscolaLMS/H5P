<?php

namespace EscolaLms\HeadlessH5P\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface EditorApiSwagger
{


    /**
     *
     * @OA\Schema(
     *      schema="H5PLibraryForEditor",
     *      type="object",
     *      @OA\Property(
     *          property="css",
     *          description="array of css dependencies",
     *          type="array",
     *          @OA\Items(type="string")
     *      ),
     *      @OA\Property(
     *          property="defaultLanguage",
     *          description="",
     *          type="string",
     *      ),
     *      @OA\Property(
     *          property="javascript",
     *          description="array of js dependencies",
     *          type="array",
     *          @OA\Items(type="string")
     *      ),
     *      @OA\Property(
     *          property="language",
     *          description="",
     *          type="string",
     *      ),
     *      @OA\Property(
     *          property="languages",
     *          description="array of possible languages",
      *         type="array",
     *          @OA\Items(type="string")
     *      ),
     *      @OA\Property(
     *          property="name",
     *          description="machine name of the library",
     *          type="string",
     *          example="H5P.ArithmeticQuiz"
     *      ),
     *      @OA\Property(
     *          property="semantics",
     *          description="array of objects with keys: name, type, label, importance, widget, etc",
     *          type="array",
     *          @OA\Items(type="object")
     *      ),
     *      @OA\Property(
     *          property="title",
     *          description="Title",
     *          type="string",
     *          example="Arithmetic Quiz"
     *      ),
     *      @OA\Property(
     *          property="translations",
     *          description="",
     *          type="string",
     *          description="array of possible languages",
     *          type="array",
     *          @OA\Items(type="string")
     *      ),
     *      @OA\Property(
     *          property="upgradesScript",
     *          description="",
     *          type="boolean",
     *      ),
     *      @OA\Property(
     *          property="version",
     *          description="has `major` and `minor` keys",
     *          type="object",
     *      )
     * )
     */

    /**
     *
     * @OA\Schema(
     *      schema="H5PEditorSettings",
     *      type="object",
     *      @OA\Property(
     *          property="ajax",
     *          description="array of css dependencies",
     *          type="object",
     *          allOf={
     *                  @OA\Schema(
     *                           @OA\Property(property="setFinished"),
     *                           @OA\Property(property="ajaxSetFinished"),
     *                    )
     *          },
     *      ),
     *      @OA\Property(
     *          property="baseUrl",
     *          description="",
     *          type="string",
     *      ),
     *      @OA\Property(
     *          property="l10n",
     *          description="transaltions",
     *          type="object",
     *      ),
     *      @OA\Property(
     *          property="contents",
     *          description="contents if editing extisting content. key is in form of `cid-{id}`",
     *          type="object",
     *      ),
     *      @OA\Property(
     *          property="editor",
     *          description="",
     *          type="object",
     *          allOf={
     *                  @OA\Schema(
     *                           @OA\Property(
     *                              property="ajaxPath",
     *                              description="",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="assets",
     *                              description="array of dependencies",
     *                              type="object",
     *                              allOf={
     *                                  @OA\Schema(
     *                                      @OA\Property(property="css", type="array", @OA\Items(type="string")),
     *                                      @OA\Property(property="js", type="array", @OA\Items(type="string")),
     *                                  )
     *                              },
     *                          ),
     *                          @OA\Property(
     *                              property="apiVersion",
     *                              description="versio of h5p API",
     *                              type="object",
     *                              allOf={
     *                                  @OA\Schema(
     *                                      @OA\Property(property="majorVersion", type="integer"),
     *                                      @OA\Property(property="minorVersion", type="integer"),
     *                                  )
     *                              },
     *                          ),
     *                          @OA\Property(
     *                              property="copyrightSemantics",
     *                              description="copyright Semantics",
     *                              type="object",
     *                          ),
     *                          @OA\Property(
     *                              property="deleteMessage",
     *                              description="delete Message",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="fileIcon",
     *                              description="",
     *                              type="object",
     *                          ),
     *                          @OA\Property(
     *                              property="filesPath",
     *                              description="array of css dependencies",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="libraryUrl",
     *                              description="array of css dependencies",
     *                              type="string",
     *                          ),
     *                          @OA\Property(
     *                              property="metadataSemantics",
     *                              description="",
     *                              type="array",
     *                              @OA\Items(type="object")
     *                          ),
     *                  )
     *          },
     *      ),
     *      @OA\Property(
     *          property="core",
     *          description="",
     *          type="object",
     *          allOf={
     *                  @OA\Schema(
     *                           @OA\Property(
     *                              property="scripts",
     *                              description="array of css dependencies",
     *                              type="array",
     *                              @OA\Items(type="string")
     *                          ),
     *                          @OA\Property(
     *                              property="styles",
     *                              description="array of css dependencies",
     *                              type="array",
     *                              @OA\Items(type="string")
     *                          ),
     *                  )
     *          },
     *      ),
     *      @OA\Property(
     *          property="languages",
     *          description="array of possible languages",
      *         type="array",
     *          @OA\Items(type="string")
     *      ),
     *      @OA\Property(
     *          property="name",
     *          description="machine name of the library",
     *          type="string",
     *          example="H5P.ArithmeticQuiz"
     *      ),
     *      @OA\Property(
     *          property="semantics",
     *          description="array of objects with keys: name, type, label, importance, widget, etc",
     *          type="array",
     *          @OA\Items(type="object")
     *      ),
     *      @OA\Property(
     *          property="title",
     *          description="Title",
     *          type="string",
     *          example="Arithmetic Quiz"
     *      ),
     *      @OA\Property(
     *          property="translations",
     *          description="",
     *          type="string",
     *          description="array of possible languages",
     *          type="array",
     *          @OA\Items(type="string")
     *      ),
     *      @OA\Property(
     *          property="upgradesScript",
     *          description="",
     *          type="boolean",
     *      ),
     *      @OA\Property(
     *          property="version",
     *          description="has `major` and `minor` keys",
     *          type="object",
     *      ),
     *      @OA\Property(
     *          property="nodeVersionId",
     *          description="ID of content if editing existsing content",
     *          type="integer",
     *      )
     * )
     */


    /**
    * @OA\Get(
    *      path="/api/hh5p/editor",
    *      summary="Editor settings ",
    *      tags={"H5P"},
    *      description="Editor settings",
    *      @OA\Parameter(
    *          name="id",
    *          description="Id of Content from DB. For initial editor (new content) id should be null",
    *          in="query",
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
    public function __invoke(Request $request, int $id): JsonResponse;
}
