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
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'library_id' => $this->library_id,
            'parameters' => $this->parameters,
            'params' => $this->params,
            'metadata' => $this->metadata,
            'slug' => $this->slug,
            'filtered' => $this->filtered,
            'disable' => $this->disable,
            'embed_type' => $this->embed_type,
            'nonce' => $this->nonce,
        ];
    }
}
