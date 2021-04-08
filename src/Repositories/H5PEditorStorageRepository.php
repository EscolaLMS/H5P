<?php
namespace EscolaLms\HeadlessH5P\Repositories;

use H5peditorStorage;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Helpers\Helpers;

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
    public function getLanguage($machineName, $majorVersion, $minorVersion, $language)
    {
        // TODO, languge, before save it to DB

        /*
        //        $language = 'ja';
        // Load translation field from DB
        $return = DB::select(
            'SELECT hlt.translation FROM h5p_libraries_languages hlt
           JOIN h5p_libraries hl ON hl.id = hlt.library_id
          WHERE hl.name = ?
            AND hl.major_version = ?
            AND hl.minor_version = ?
            AND hlt.language_code = ?',
            [$machineName, $majorVersion, $minorVersion, $language]
        );

        return $return ? $return[0]->translation : null;
        */
        return null;
    }

    /**
     * Load a list of available language codes from the database.
     *
     * @param string $machineName The machine readable name of the library(content type)
     * @param int $majorVersion Major part of version number
     * @param int $minorVersion Minor part of version number
     * @return array List of possible language codes
     */
    public function getAvailableLanguages($machineName, $majorVersion, $minorVersion)
    {
        // TODO this shoule return something
        return [];
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
        // TODO

        // check against specific libraries

        $libraries_result = H5PLibrary::where('runnable', 1)
                ->whereNotNull('semantics')
                ->orderBy('name', 'ASC')
                ->get();

        Helpers::fixCaseKeysArray(['majorVersion', 'minorVersion', 'patchVersion'], $libraries_result);

        return $libraries_result;

        $return = [];

        if ($libraries !== null) {
            // Get details for the specified libraries only.
            foreach ($libraries as $library) {
                // Look for library
                $details = H5PLibrary::where('name', $library->name)
                    ->where('major_version', $library->majorVersion)
                    ->where('minor_version', $library->minorVersion)
                    ->whereNotNull('semantics')
                    ->first();

                if ($details) {
                    // Library found, add details to list
                    $library->tutorialUrl = $details->tutorial_url;
                    $library->title = $details->title;
                    $library->runnable = $details->runnable;
                    $library->restricted = $details->restricted === '1' ? true : false;
                    $return[] = $library;
                }
            }
        } else {

            // Load all libraries
            $libraries = [];

            $libraries_result = H5PLibrary::where('runnable', 1)
                ->whereNotNull('semantics')
                ->orderBy('name', 'ASC')
                ->get();

            Helpers::fixCaseKeysArray(['majorVersion', 'minorVersion', 'patchVersion'], $libraries_result);


            /*
            // 모든 버전의 라리브러리가 로드되므로 하나의 가장 최신 라이브러리를 찾는 부분
            foreach ($libraries_result as $library) {
                // Make sure we only display the newest version of a library.
                foreach ($libraries as $key => $existingLibrary) {
                    if ($library->name === $existingLibrary->name) {
                        // Found library with same name, check versions
                        if (($library->majorVersion === $existingLibrary->majorVersion &&
                            $library->minorVersion > $existingLibrary->minorVersion) ||
                            ($library->majorVersion > $existingLibrary->majorVersion)) {
                            // This is a newer version
                            $existingLibrary->isOld = true;
                        } else {
                            // This is an older version
                            $library->isOld = true;
                        }
                    }
                }
                // Check to see if content type should be restricted
                $library->restricted = $library->restricted === '1' ? true : false;

                // Add new library
                $return[] = $library;
            }
            */
        }

        return $return;
        // TODO
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
    public static function markFileForCleanup($file, $content_id)
    {
    }
    /**
     * Clean up temporary files
     *
     * @param string $filePath Path to file or directory
     */
    public static function removeTemporarilySavedFiles($filePath)
    {
    }
}
