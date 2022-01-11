<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class LibraryListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('list', H5PLibrary::class);
    }

    public function rules(): array
    {
        return [];
    }
}
