<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ContentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $h5pContent = H5PContent::findOrFail($this->route('id'));
        return Gate::allows('update', $h5pContent);
    }

    public function rules(): array
    {
        return [
            'library' => ['required', 'string'],
            'params'  => ['required', 'string'],
            'nonce'  => ['required', 'string'],
        ];
    }
}
