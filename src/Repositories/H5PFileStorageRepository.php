<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use Exception;
use H5PCore;
use H5peditorFile;
use H5PFileStorage;
use H5PDefaultStorage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function PHPUnit\Framework\assertTrue;

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

        $this->copyFiles($library['uploadDirectory'], $dest);
    }

    private static function libraryToFolderName($library) {
        $name = $library['machineName'] ?? $library['name'];
        $includePatchVersion = $library['patchVersionInFolderName'] ?? false;

        return "{$name}-{$library['majorVersion']}.{$library['minorVersion']}" . ($includePatchVersion ? ".{$library['patchVersion']}" : '');
    }

    /**
     * @throws Exception
     */
    public function copyFiles($source, $destination) {
        if (!$this->isDirReady($destination)) {
            throw new Exception('unabletocopy');
        }

        $ignoredFiles = $this->ignoredFilesProvider("{$source}/.h5pignore");

        $dir = opendir($source);
        if ($dir === false) {
            trigger_error('Unable to open directory ' . $source, E_USER_WARNING);
            throw new Exception('unabletocopy');
        }

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..') && $file != '.git' && $file != '.gitignore' && !in_array($file, $ignoredFiles)) {
                if (is_dir("{$source}/{$file}")) {
                    $this->copyFiles("{$source}/{$file}", "{$destination}/{$file}");
                }
                else {
                    if (config('filesystems.default') === 's3') {
                        $folder = Str::after($destination, env('AWS_URL', '/'));
                        Log::info('folder: ' . $folder);
                        Storage::putFileAs($folder, new File("{$source}/{$file}"), $file);
                    } else {
                        copy("{$source}/{$file}", "{$destination}/{$file}");
                    }
                }
            }
        }

        closedir($dir);
    }

    private function ignoredFilesProvider($file)
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

    private function isDirReady($path): bool
    {
        if (config('filesystems.default') === 's3') {
            if (!Storage::exists($path)) {
                $path = config('filesystems.default') === 's3' ? Str::after($path, env('AWS_URL', '/')) : $path;
                Storage::makeDirectory($path);
            }
            if (!Storage::directoryExists($path)) {
                trigger_error('Path is not a directory ' . $path, E_USER_WARNING);
                return false;
            }
        } else {
            if (!file_exists($path)) {
                $parent = preg_replace("/\/[^\/]+\/?$/", '', $path);
                if (!$this->isDirReady($parent)) {
                    return false;
                }

                mkdir($path, 0777, true);

                if (!is_dir($path)) {
                    trigger_error('Path is not a directory ' . $path, E_USER_WARNING);
                    return false;
                }

                if (!is_writable($path)) {
                    trigger_error('Unable to write to ' . $path . ' – check directory permissions –', E_USER_WARNING);
                    return false;
                }
            }
        }
        return true;
    }

    public function cacheAssets(&$files, $key)
    {
        foreach ($files as $type => $assets) {
            if (empty($assets)) {
                continue; // Skip no assets
            }

            $content = '';
            foreach ($assets as $asset) {
                // Get content from asset file
                $assetContent = Storage::get(config('hh5p.url') . $asset->path);

                $cssRelPath = ltrim(preg_replace('/[^\/]+$/', '', $asset->path), '/');

                // Get file content and concatenate
                if ($type === 'scripts') {
                    $content .= $assetContent . ";\n";
                }
                else {
                    // Rewrite relative URLs used inside stylesheets
                    $content .= preg_replace_callback(
                            '/url\([\'"]?([^"\')]+)[\'"]?\)/i',
                            function ($matches) use ($cssRelPath) {
                                if (preg_match("/^(data:|([a-z0-9]+:)?\/)/i", $matches[1]) === 1) {
                                    return $matches[0]; // Not relative, skip
                                }
                                return 'url("../' . $cssRelPath . $matches[1] . '")';
                            },
                            $assetContent) . "\n";
                }
            }

            $this->isDirReady("{$this->path}/cachedassets");
            $ext = ($type === 'scripts' ? 'js' : 'css');
            $outputfile = "/cachedassets/{$key}.{$ext}";
            Storage::put(config('hh5p.url') . $outputfile, $content);

            $files[$type] = array((object) array(
                'path' => $outputfile,
                'version' => ''
            ));
        }
    }

    public function saveFile($file, $contentId): H5peditorFile
    {
        // Prepare directory
        if (empty($contentId)) {
            // Should be in editor tmp folder
            $path = $this->getEditorPath();
        }
        else {
            // Should be in content folder
            $path = $this->path . '/content/' . $contentId;
        }
        $path .= '/' . $file->getType() . 's';
        $this->isDirReady($path);

        Storage::putFileAs(Str::after($path, env('AWS_URL')), $_FILES['file']['tmp_name'], $file->getName());

        return $file;
    }

    private function getEditorPath()
    {
        return ($this->altEditorPath !== NULL ? $this->altEditorPath : "{$this->path}/editor");
    }

    public function saveContent($source, $content): void
    {
        $dest = "{$this->path}/content/{$content['id']}";

        // Remove any old content
        H5PCore::deleteFileTree($dest);

        $this->copyFiles($source, $dest);
    }
}
