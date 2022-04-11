<?php

namespace EscolaLms\HeadlessH5P\Models;

use Illuminate\Database\Eloquent\Model;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5PLibraryLanguage extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'hh5p_libraries_languages';

    protected $fillable = [
        'library_id',
        'language_code',
        'translation',
    ];


    public function getTranslationAttribute($value)
    {
        return json_decode($value);
    }


    public function library():BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }
}
