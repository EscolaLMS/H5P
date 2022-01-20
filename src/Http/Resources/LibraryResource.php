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
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'major_version' => $this->major_version,
            'minor_version' => $this->minor_version,
            'patch_version' => $this->patch_version,
            'runnable' => $this->runnable,
            'restricted' => $this->restricted,
            'fullscreen' => $this->fullscreen,
            'embed_types' => $this->embed_types,
            'preloaded_js' => $this->preloaded_js,
            'preloaded_css' => $this->preloaded_css,
            'drop_library_css' => $this->drop_library_css,
            'semantics' => $this->semantics,
            'tutorial_url' => $this->tutorial_url,
            'has_icon' => $this->has_icon,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'machineName' => $this->machineName,
            'uberName' => $this->uberName,
            'majorVersion' => $this->majorVersion,
            'minorVersion' => $this->minorVersion,
            'patchVersion' => $this->patchVersion,
            'preloadedJs' => $this->preloadedJs,
            'preloadedCss' => $this->preloadedCss,
            'dropLibraryCss' => $this->dropLibraryCss,
            'tutorialUrl' => $this->tutorialUrl,
            'hasIcon' => $this->hasIcon,
            'libraryId' => $this->libraryId,
            'children' => $this->children,
            'languages' => $this->languages,
        ];
    }
}
