<?php

namespace EscolaLms\HeadlessH5P\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LibraryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'h5p_file' => 'required||max:100000',
        ];
    }
}
