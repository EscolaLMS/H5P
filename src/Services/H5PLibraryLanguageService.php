<?php

namespace EscolaLms\HeadlessH5P\Services;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;
use EscolaLms\HeadlessH5P\Services\Contracts\H5PLibraryLanguageServiceContract;
use Illuminate\Support\Facades\File;

class H5PLibraryLanguageService implements H5PLibraryLanguageServiceContract
{
    public function getTranslation(string $langCode, string $libraryName): ?string
    {
        $semantics = __DIR__ . '/../../resources/lang/' . $langCode . '/' . $libraryName . '/' . $langCode . '.json';

        return File::exists($semantics) ? File::get($semantics) : null;
    }

    public function getTranslationString(mixed $translation): string
    {
        if (empty($translation)) {
            return '';
        }

        return is_string($translation) ? $translation : json_encode($translation);
    }

    public function update(H5PLibraryLanguage $libraryLanguage, H5PLibrary $library, string $languageCode): H5PLibraryLanguage
    {
        $translation = $this->getTranslation($languageCode, $library->directoryName);

        if (empty($translation)) {
            return $libraryLanguage;
        }

        if (json_decode($translation) == $libraryLanguage->translation) {
            return $libraryLanguage;
        }

        $libraryLanguage->translation = $translation;
        $libraryLanguage->save();

        return $libraryLanguage;
    }

    public function create(H5PLibrary $library, string $languageCode): ?H5PLibraryLanguage
    {
        $translation = $this->getTranslation($languageCode, $library->directoryName);

        if (empty($translation)) {
            return null;
        }

        return H5PLibraryLanguage::firstOrCreate([
            'library_id' => $library->getKey(),
            'language_code' => $languageCode,
            'translation' => $translation,
        ]);
    }

    public function createDefaults(H5PLibrary $library, string $languageCode, string $translation): ?H5PLibraryLanguage
    {
        $localTranslation = $this->getTranslation($languageCode, $library->directoryName);
        return H5PLibraryLanguage::firstOrCreate([
            'library_id' => $library->getKey(),
            'language_code' => $languageCode,
            'translation' => $localTranslation ?: $translation,
        ]);

    }
}
