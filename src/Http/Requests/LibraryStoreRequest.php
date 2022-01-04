<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class LibraryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route('id')) {
            $h5PContentRepository = app(H5PContentRepositoryContract::class);
            $h5pLibrary = $h5PContentRepository->getLibraryById($this->route('id'));

            return Gate::allows('update', $h5pLibrary);
        }

        return Gate::allows('update', H5PLibrary::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'h5p_file' => ['required', 'max:100000']
        ];
    }
}
