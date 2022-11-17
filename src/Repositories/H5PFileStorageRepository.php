<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use Exception;
use H5PFileStorage;
use H5PDefaultStorage;

class H5PFileStorageRepository extends H5PDefaultStorage implements H5PFileStorage
{
    private string $path;

    private ?string $altEditorPath;

    function __construct($path, $altEditorPath = null) {
        parent::__construct($path, $altEditorPath);
        $this->path = $path;
        $this->altEditorPath = $altEditorPath;
    }

    public function saveLibrary($library)
    {
        $dest = $this->path . '/libraries/' . $this->libraryToFolderName($library);

        $this->copyFileTree($library['uploadDirectory'], $dest);
    }

    private static function libraryToFolderName($library) {
        $name = $library['machineName'] ?? $library['name'];
        $includePatchVersion = $library['patchVersionInFolderName'] ?? false;

        return "{$name}-{$library['majorVersion']}.{$library['minorVersion']}" . ($includePatchVersion ? ".{$library['patchVersion']}" : '');
    }

    /**
     * @throws Exception
     */
    private function copyFileTree($source, $destination) {
        if (!$this->dirReady($destination)) {
            throw new Exception('unabletocopy');
        }

        $ignoredFiles = $this->getIgnoredFiles("{$source}/.h5pignore");

        $dir = opendir($source);
        if ($dir === false) {
            trigger_error('Unable to open directory ' . $source, E_USER_WARNING);
            throw new Exception('unabletocopy');
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..') && $file != '.git' && $file != '.gitignore' && !in_array($file, $ignoredFiles)) {
                if (is_dir("{$source}/{$file}")) {
                    $this->copyFileTree("{$source}/{$file}", "{$destination}/{$file}");
                }
                else {
                    copy("{$source}/{$file}", "{$destination}/{$file}");
                }
            }
        }

        closedir($dir);
    }

    private function getIgnoredFiles($file): array|bool
    {
        if (file_exists($file) === false) {
            return [];
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            return [];
        }

        return preg_split('/\s+/', $contents);
    }

    private function dirReady($path): bool
    {
        if (!file_exists($path)) {
            $parent = preg_replace("/\/[^\/]+\/?$/", '', $path);
            if (!$this->dirReady($parent)) {
                return false;
            }

            mkdir($path, 0777, true);
        }

        if (!is_dir($path)) {
            trigger_error('Path is not a directory ' . $path, E_USER_WARNING);
            return false;
        }

        if (!is_writable($path)) {
            trigger_error('Unable to write to ' . $path . ' – check directory permissions –', E_USER_WARNING);
            return false;
        }

        return true;
    }
}
