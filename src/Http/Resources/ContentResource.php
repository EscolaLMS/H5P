<?php

namespace EscolaLms\HeadlessH5P\Http\Resources;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentResource extends JsonResource
{
    public function __construct(H5PContent $content)
    {
        $this->resource = $content;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'uuid' => $this->resource->uuid,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'user_id' => $this->resource->user_id,
            'title' => $this->resource->title,
            'library_id' => $this->resource->library_id,
            'parameters' => $this->resource->parameters,
            'params' => $this->resource->params,
            'metadata' => $this->resource->metadata,
            'slug' => $this->resource->slug,
            'filtered' => $this->resource->filtered,
            'disable' => $this->resource->disable,
            'embed_type' => $this->resource->embed_type,
            'nonce' => $this->resource->nonce,
        ];
    }
}
