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
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'machineName' => $this->machineName,
            'uberName' => $this->uberName,
            'libraryId' => $this->libraryId,
        ];
    }
}
