<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class LibraryUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('upload', H5PLibrary::class);
    }

    public function rules(): array
    {
        return [
            'id' => ['required'],
            'contentId' => ['required'],
            'h5p' => ['required', 'max:100000']
        ];
    }

    public function getId()
    {
        return $this->get('id');
    }

    public function getContentId()
    {
        return $this->get('contentId');
    }

    public function getH5PFile(): UploadedFile
    {
        return $this->file('h5p');
    }
}
