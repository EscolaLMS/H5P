<?php

namespace EscolaLms\HeadlessH5P\Http\Resources;

use EscolaLms\HeadlessH5P\Traits\ResourceExtendable;
use Illuminate\Http\Resources\Json\JsonResource;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

class ContentIndexResource extends JsonResource
{
    use ResourceExtendable;

    public function toArray($request): array
    {
        $lib = H5PLibrary::find($this->resource->library_id);

        $fields = [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'user_id' => $this->resource->user_id,
            'author' => $this->resource->author,
            'title' => $this->resource->title,
            'library_id' => $this->resource->library_id,
            'library' => isset($lib) ? (new LibraryIndexResource($lib))->toArray() : null,
            'slug' => $this->resource->slug,
            'filtered' => $this->resource->filtered,
            'disable' => $this->resource->disable,
        ];

        return self::apply($fields, $this);
    }
}
