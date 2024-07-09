<?php

namespace EscolaLms\HeadlessH5P\Http\Resources;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class LibraryIndexResource extends JsonResource
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
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'machineName' => $this->resource->machineName,
            'uberName' => $this->resource->uberName,
            'libraryId' => $this->resource->libraryId,
        ];
    }
}
