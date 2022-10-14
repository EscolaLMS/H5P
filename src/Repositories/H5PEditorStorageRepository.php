<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use H5peditorStorage;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;
use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PTempFile;
use EscolaLms\HeadlessH5P\Helpers\Helpers;
use Illuminate\Support\Facades\File;

/**
 * A defined interface for the editor to communicate with the database of the
 * web system.
 */
class H5PEditorStorageRepository implements H5peditorStorage
{

    /**
     * Load language file(JSON) from database.
     * This is used to translate the editor fields(title, description etc.)
     *
     * @param string $name The machine readable name of the library(content type)
     * @param int $major Major part of version number
     * @param int $minor Minor part of version number
     * @param string $lang Language code
     * @return string Translation in JSON format
     */
    public function getLanguage($machineName, $majorVersion, $minorVersion, $language): ?string
    {
        if (!isset($language)) {
            return null;
        }

        $library = H5PLibrary::where([
            ['major_version',  $majorVersion],
            ['minor_version', $minorVersion],
            ['name', $machineName],
        ])->first();

        if ($library) {
            $libraryLanguage = H5PLibraryLanguage::where([
                ['library_id', $library->id],
                ['language_code',  $language]
            ])->first();

            if ($libraryLanguage) {
                $this->updateH5pLibraryLanguage($library, $libraryLanguage, $language);
                return $this->processTranslation($libraryLanguage->translation);
            }

            // if is empty try to create from local translation files
            if (empty($libraryLanguage)) {
                $libraryLanguage = $this->createH5PLibraryLanguage($library, $language);
                return $libraryLanguage ? $this->processTranslation($libraryLanguage->translation) : null;
            }
        }

        return null;
    }

    // TODO move to helper/service class
    private function processTranslation($translation): bool|string
    {
        if (empty($translation)) {
            return '';
        }

        return is_string($translation) ? $translation : json_encode($translation);
    }

    private function getTranslation(string $langCode, string $libraryName): ?string
    {
        $semantics = __DIR__ . '/../../resources/lang/' . $langCode . '/' . $libraryName . '/' . $langCode . '.json';
        return File::exists($semantics) ? File::get($semantics) : null;
    }

    private function updateH5pLibraryLanguage(H5PLibrary $library, H5PLibraryLanguage $libraryLanguage, string $languageCode): ?H5PLibraryLanguage
    {
        $libraryName = $library->name . '-' . $library->major_version . '.' . $library->minor_version;
        $translation = $this->getTranslation($languageCode, $libraryName);

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

    private function createH5PLibraryLanguage(H5PLibrary $library, string $languageCode): ?H5PLibraryLanguage
    {
        $libraryName = $library->machineName . '-' . $library->majorVersion . '.' . $library->minorVersion;
        $translation = $this->getTranslation($languageCode, $libraryName);

        if (empty($translation)) {
            return null;
        }

        return H5PLibraryLanguage::firstOrCreate([
            'library_id' => $library->getKey(),
            'language_code' => $languageCode,
            'translation' => $translation,
        ]);
    }

    /**
     * Load a list of available language codes from the database.
     *
     * @param string $machineName The machine readable name of the library(content type)
     * @param int $majorVersion Major part of version number
     * @param int $minorVersion Minor part of version number
     * @return array List of possible language codes
     */
    public function getAvailableLanguages($machineName, $majorVersion, $minorVersion): array
    {
        return H5PLibrary::with('languages')
            ->where('name', '=', $machineName)
            ->where('major_version', '=', $majorVersion)
            ->where('minor_version', '=', $minorVersion)
            ->get()
            ->map(fn($item) => $item->languages->map(fn($lang) => $lang->language_code))
            ->filter(fn($item) => $item !== config('hh5p.language'))
            ->flatten()
            ->prepend(config('hh5p.language'))
            ->toArray();
    }
    /**
     * "Callback" for mark the given file as a permanent file.
     * Used when saving content that has new uploaded files.
     *
     * @param int $fileId
     */
    public function keepFile($fileId)
    {
    }

    /**
     * Decides which content types the editor should have.
     *
     * Two usecases:
     * 1. No input, will list all the available content types.
     * 2. Libraries supported are specified, load additional data and verify
     * that the content types are available. Used by e.g. the Presentation Tool
     * Editor that already knows which content types are supported in its
     * slides.
     *
     * @param array $libraries List of library names + version to load info for
     * @return array List of all libraries loaded
     */
    public function getLibraries($libraries = null)
    {
        if (isset($libraries)) {
            return collect($libraries)
                ->map(fn ($library) => H5PLibrary::where([
                    ['name', $library->name],
                    ['major_version', $library->majorVersion],
                    ['minor_version', $library->minorVersion]
                ])->first())
                ->reject(fn($library) => !$library)
                ->all();
        }

        $libraries_result = H5PLibrary::where('runnable', 1)
            ->whereNotNull('semantics')
            ->orderBy('title', 'ASC')
            ->get()
            ->all();

        Helpers::fixCaseKeysArray(['majorVersion', 'minorVersion', 'patchVersion'], $libraries_result);

        return $libraries_result;
    }
    /**
     * Alter styles and scripts
     *
     * @param array $files
     *  List of files as objects with path and version as properties
     * @param array $libraries
     *  List of libraries indexed by machineName with objects as values. The objects
     *  have majorVersion and minorVersion as properties.
     */
    public function alterLibraryFiles(&$files, $libraries)
    {
    }

    /**
     * Saves a file or moves it temporarily. This is often necessary in order to
     * validate and store uploaded or fetched H5Ps.
     *
     * @param string $data Uri of data that should be saved as a temporary file
     * @param boolean $move_file Can be set to TRUE to move the data instead of saving it
     *
     * @return bool|object Returns false if saving failed or the path to the file
     *  if saving succeeded
     */
    public static function saveFileTemporarily($data, $move_file)
    {
    }

    /**
     * Marks a file for later cleanup, useful when files are not instantly cleaned
     * up. E.g. for files that are uploaded through the editor.
     *
     * @param H5peditorFile
     * @param $content_id
     */
    public static function markFileForCleanup($file, $nonce)
    {
        $content = H5PContent::where('nonce', $nonce)->first();

        $path = is_null($content) ? '/editor' : '/content/' . $content->id;
        $path .= '/' . $file->getType() . 's/' . $file->getName();

        return H5PTempFile::create(['path' => $path, 'nonce' => $nonce]);
    }

    /**
     * Clean up temporary files
     *
     * @param string $filePath Path to file or directory
     */
    public static function removeTemporarilySavedFiles($filePath)
    {
        if (is_dir($filePath)) {
            Helpers::deleteFileTree($filePath);
        }
        else {
            unlink($filePath);
        }
    }
}
