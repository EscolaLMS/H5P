<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ContentListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('list', H5PContent::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string'],
            'library_id' => ['sometimes', 'nullable', 'integer'],
            'order_by' => ['sometimes', 'nullable', 'string', 'in:id,title,library_id,library_title'],
            'order' => ['sometimes', 'nullable', 'string', 'in:ASC,DESC'],
        ];
    }
}
