<?php

namespace EscolaLms\HeadlessH5P\Services;

use Exception;
use H5PCore;
use H5PExport;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class H5PExportService extends H5PExport
{
    public function createExportFile($content) {
        // Get path to temporary folder, where export will be contained
        $tmpPath = $this->h5pC->fs->getTmpPath();
        Storage::createDirectory($tmpPath);

        try {
            // Create content folder and populate with files
            $this->h5pC->fs->exportContent($content['id'], "{$tmpPath}/content");
        }
        catch (Exception $e) {
            $this->h5pF->setErrorMessage($this->h5pF->t($e->getMessage()), 'failed-creating-export-file');
            H5PCore::deleteFileTree($tmpPath);
            return FALSE;
        }

        // Update content.json with content from database
        Storage::put("{$tmpPath}/content/content.json", $content['filtered']);

        // Make embedType into an array
        $embedTypes = explode(', ', $content['embedType']);

        // Build h5p.json, the en-/de-coding will ensure proper escaping
        $h5pJson = array (
            'title' => self::revertH5PEditorTextEscape($content['title']),
            'language' => (isset($content['language']) && strlen(trim($content['language'])) !== 0) ? $content['language'] : 'und',
            'mainLibrary' => $content['library']['name'],
            'embedTypes' => $embedTypes
        );

        foreach(array('authors', 'source', 'license', 'licenseVersion', 'licenseExtras' ,'yearFrom', 'yearTo', 'changes', 'authorComments', 'defaultLanguage') as $field) {
            if (isset($content['metadata'][$field]) && $content['metadata'][$field] !== '') {
                if (($field !== 'authors' && $field !== 'changes') || (count($content['metadata'][$field]) > 0)) {
                    $h5pJson[$field] = json_decode(json_encode($content['metadata'][$field], TRUE));
                }
            }
        }

        // Remove all values that are not set
        foreach ($h5pJson as $key => $value) {
            if (!isset($value)) {
                unset($h5pJson[$key]);
            }
        }

        // Add dependencies to h5p
        foreach ($content['dependencies'] as $dependency) {
            $library = $dependency['library'];

            try {
                $exportFolder = NULL;

                // Determine path of export library
                if (isset($this->h5pC) && isset($this->h5pC->h5pD)) {

                    // Tries to find library in development folder
                    $isDevLibrary = $this->h5pC->h5pD->getLibrary(
                        $library['machineName'],
                        $library['majorVersion'],
                        $library['minorVersion']
                    );

                    if ($isDevLibrary !== NULL && isset($library['path'])) {
                        $exportFolder = "/" . $library['path'];
                    }
                }

                // Export required libraries
                $this->h5pC->fs->exportLibrary($library, $tmpPath, $exportFolder);
            }
            catch (Exception $e) {
                $this->h5pF->setErrorMessage($this->h5pF->t($e->getMessage()), 'failed-creating-export-file');
                H5PCore::deleteFileTree($tmpPath);
                return FALSE;
            }

            // Do not add editor dependencies to h5p json.
            if ($dependency['type'] === 'editor') {
                continue;
            }

            // Add to h5p.json dependencies
            $h5pJson[$dependency['type'] . 'Dependencies'][] = array(
                'machineName' => $library['machineName'],
                'majorVersion' => $library['majorVersion'],
                'minorVersion' => $library['minorVersion']
            );
        }

        // Save h5p.json
        $results = print_r(json_encode($h5pJson), true);
        Storage::put("{$tmpPath}/h5p.json", $results);

        // Get a complete file list from our tmp dir
        $files = array();
        self::populateFileList($tmpPath, $files);

        // Get path to temporary export target file
        $tmpFile = $this->h5pC->fs->getTmpPath();
        $zipPath = storage_path('app' . $tmpFile);

        // Create new zip instance.
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Add all the files from the tmp dir.
        foreach ($files as $file) {
            // Please note that the zip format has no concept of folders, we must
            // use forward slashes to separate our directories.
            if (Storage::exists($file->absolutePath)) {
                $zip->addFromString($file->relativePath, Storage::get($file->absolutePath));
            }
        }

        // Close zip and remove tmp dir
        $zip->close();
        Storage::putFileAs('h5p/temp', new File($zipPath), Str::afterLast($tmpFile, '/'));
        unlink($zipPath);
        H5PCore::deleteFileTree($tmpPath);

        $filename = $content['slug'] . '-' . $content['id'] . '.h5p';
        try {
            // Save export
            $this->h5pC->fs->saveExport($tmpFile, $filename);
        }
        catch (Exception $e) {
            $this->h5pF->setErrorMessage($this->h5pF->t($e->getMessage()), 'failed-creating-export-file');
            return false;
        }

        Storage::delete($tmpFile);
        Storage::deleteDirectory($tmpPath);
        $this->h5pF->afterExportCreated($content, $filename);

        return true;
    }

    private static function populateFileList($dir, &$files, $relative = '') {
        $strip = strlen($dir);
        $contents = Storage::allFiles($dir);
        if (!empty($contents)) {
            foreach ($contents as $file) {
                $rel = $relative . substr($file, $strip);
                if (is_dir($file)) {
                    self::populateFileList($file, $files, $rel . '/');
                }
                else {
                    $files[] = (object) array(
                        'absolutePath' => $file,
                        'relativePath' => $rel
                    );
                }
            }
        }
    }

    private static function revertH5PEditorTextEscape($value) {
        return str_replace('&lt;', '<', str_replace('&gt;', '>', str_replace('&#039;', "'", str_replace('&quot;', '"', $value))));
    }
}
