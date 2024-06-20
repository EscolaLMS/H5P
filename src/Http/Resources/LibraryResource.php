<?php

namespace EscolaLms\HeadlessH5P\Http\Resources;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class LibraryResource extends JsonResource
{
    public function __construct(H5PLibrary $library)
    {
        $this->resource = $library;
    }

    public function toArray($request = null): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'title' => $this->resource->title,
            'major_version' => $this->resource->major_version,
            'minor_version' => $this->resource->minor_version,
            'patch_version' => $this->resource->patch_version,
            'runnable' => $this->resource->runnable,
            'restricted' => $this->resource->restricted,
            'fullscreen' => $this->resource->fullscreen,
            'embed_types' => $this->resource->embed_types,
            'preloaded_js' => $this->resource->preloaded_js,
            'preloaded_css' => $this->resource->preloaded_css,
            'drop_library_css' => $this->resource->drop_library_css,
            'semantics' => $this->resource->semantics,
            'tutorial_url' => $this->resource->tutorial_url,
            'has_icon' => $this->resource->has_icon,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'machineName' => $this->resource->machineName,
            'uberName' => $this->resource->uberName,
            'majorVersion' => $this->resource->majorVersion,
            'minorVersion' => $this->resource->minorVersion,
            'patchVersion' => $this->resource->patchVersion,
            'preloadedJs' => $this->resource->preloadedJs,
            'preloadedCss' => $this->resource->preloadedCss,
            'dropLibraryCss' => $this->resource->dropLibraryCss,
            'tutorialUrl' => $this->resource->tutorialUrl,
            'hasIcon' => $this->resource->hasIcon,
            'libraryId' => $this->resource->libraryId,
            'languages' => $this->resource->languages,
            'contentsCount' => $this->resource->contentsCount,
            'requiredLibrariesCount' => $this->resource->requiredLibrariesCount,
        ];
    }
}
