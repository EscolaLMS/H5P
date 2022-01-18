<?php

namespace EscolaLms\HeadlessH5P\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContentIndexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'library_id' => $this->library_id,
        ];
    }
}
