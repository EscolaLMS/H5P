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
            'author' => $this->author,
            'title' => $this->title,
            'library_id' => $this->library_id,
            'library' => isset($lib) ? (new LibraryIndexResource($lib))->toArray() : null,
            'slug' => $this->slug,
            'filtered' => $this->filtered,
            'disable' => $this->disable,
        ];

        return self::apply($fields, $this);
    }
}
