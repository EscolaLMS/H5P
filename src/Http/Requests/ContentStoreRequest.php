<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ContentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->route('id')) {
            $h5PContentRepository = app(H5PContentRepositoryContract::class);
            $h5pContent = $h5PContentRepository->show($this->route('id'));

            return Gate::allows('update', $h5pContent);
        }

        return Gate::allows('update', H5PContent::class);
    }

    public function rules(): array
    {
        return [
            'title'   => ['required', 'string'],
            'library' => ['required', 'string'],
            'params'  => ['required', 'string'],
            'nonce'  => ['required', 'string'],
        ];
    }
}
