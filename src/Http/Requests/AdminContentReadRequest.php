<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use Illuminate\Support\Facades\Gate;

class AdminContentReadRequest extends ContentReadRequest
{
    public function authorize(): bool
    {
        $h5pContent = $this->getH5PContent();

        return Gate::allows('read', $h5pContent);
    }

    public function getH5PContent(): H5PContent
    {
        return H5PContent::findOrFail($this->route('id'));
    }
}
