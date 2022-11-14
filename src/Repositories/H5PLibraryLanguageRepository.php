<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PLibraryLanguageRepositoryContract;
use Illuminate\Support\Facades\File;

class H5PLibraryLanguageRepository implements H5PLibraryLanguageRepositoryContract
{
    public function getTranslation(H5PLibrary $library, string $langCode): ?string
    {
        $translation = $this->getLibraryTranslationVersion($langCode, $library->name, $library->mainVersion);

        return empty($translation) ?
            $this->getLibraryTranslation($langCode, $library->name)
            : $translation;
    }

    public function getTranslationString($translation): string
    {
        if (empty($translation)) {
            return '';
        }

        return is_string($translation) ? $translation : json_encode($translation);
    }

    public function update(H5PLibraryLanguage $libraryLanguage, H5PLibrary $library, string $languageCode): H5PLibraryLanguage
    {
        $translation = $this->getTranslation($library, $languageCode);

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
        $translation = $this->getTranslation($library, $languageCode);

        if (empty($translation)) {
            return null;
        }

        return H5PLibraryLanguage::firstOrCreate([
            'library_id' => $library->getKey(),
            'language_code' => $languageCode
        ], ['translation' => $translation,]);
    }

    public function createDefaults(H5PLibrary $library, string $languageCode, string $translation): ?H5PLibraryLanguage
    {
        $localTranslation = $this->getTranslation($library, $languageCode);

        return H5PLibraryLanguage::firstOrCreate([
            'library_id' => $library->getKey(),
            'language_code' => $languageCode,
        ], ['translation' => $localTranslation ?: $translation]);
    }

    private function getLibraryTranslation(string $langCode, string $libraryName): ?string
    {
        $semantics = __DIR__ . '/../../resources/lang/' . $langCode . '/' . $libraryName . '/' . $langCode . '.json';

        return File::exists($semantics) ? File::get($semantics) : null;
    }

    private function getLibraryTranslationVersion(string $langCode, string $libraryName, string $libraryVersion): ?string
    {
        $semantics = __DIR__ . '/../../resources/lang/' . $langCode . '/' . $libraryName . '/' . $libraryVersion . '/' . $langCode . '.json';

        return File::exists($semantics) ? File::get($semantics) : null;
    }
}
