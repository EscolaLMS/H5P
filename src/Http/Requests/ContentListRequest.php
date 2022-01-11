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
        return [];
    }
}
