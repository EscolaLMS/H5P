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
        $lib = H5PLibrary::find($this->library_id);

        $fields = [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'library_id' => $this->library_id,
            'library' => isset($lib) ? (new LibraryIndexResource($lib))->toArray() : null,
            'parameters' => $this->parameters,
            'params' => $this->params,
            'metadata' => $this->metadata,
            'slug' => $this->slug,
            'filtered' => $this->filtered,
            'disable' => $this->disable,
            'embed_type' => $this->embed_type,
            'nonce' => $this->nonce,
        ];

        return self::apply($fields, $this);
    }
}
