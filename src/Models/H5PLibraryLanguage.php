<?php

namespace EscolaLms\HeadlessH5P\Models;

use EscolaLms\HeadlessH5P\Database\Factories\H5PLibraryLanguageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $language_code
 */
class H5PLibraryLanguage extends Model
{
    use HasFactory;

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

    public function library(): BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }

    protected static function newFactory(): H5PLibraryLanguageFactory
    {
        return H5PLibraryLanguageFactory::new();
    }
}
