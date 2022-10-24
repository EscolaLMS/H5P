<?php

namespace EscolaLms\HeadlessH5P\Services\Contracts;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;

interface H5PLibraryLanguageServiceContract
{
    public function getTranslation(string $langCode, string $libraryName): ?string;

    public function getTranslationString(mixed $translation): string;

    public function update(H5PLibraryLanguage $libraryLanguage, H5PLibrary $library, string $languageCode): H5PLibraryLanguage;

    public function create(H5PLibrary $library, string $languageCode): ?H5PLibraryLanguage;

    public function createDefaults(H5PLibrary $library, string $languageCode, string $translation): ?H5PLibraryLanguage;
}
