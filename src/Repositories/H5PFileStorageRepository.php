<?php

namespace EscolaLms\HeadlessH5P\Repositories;

use Exception;
use H5PFileStorage;
use H5PDefaultStorage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class H5PFileStorageRepository extends H5PDefaultStorage implements H5PFileStorage
{
    private string $path;

    private ?string $altEditorPath;

    function __construct($path, $altEditorPath = null) {
        Log::info('H5PFileStorageRepository');
        Log::info('$path: ' . $path);
        parent::__construct($path, $altEditorPath);
        $this->path = $path;
        $this->altEditorPath = $altEditorPath;
    }

    public function saveLibrary($library)
    {
        $dest = $this->path . '/libraries/' . $this->libraryToFolderName($library);

        // TODO tutaj zerknąć
        Log::info('saveLibrary');
        Log::info('dest: ' . $dest);
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
        Log::info('copyFiles');
        if (!$this->isDirReady($destination)) {
            Log::info('!$this->isDirReady($destination)');
            throw new Exception('unabletocopy');
        }

        Log::info('$destination: ' . $destination);

        $ignoredFiles = $this->ignoredFilesProvider("{$source}/.h5pignore");

        $dir = opendir($source);
        if ($dir === false) {
            Log::info('$dir === false');
            trigger_error('Unable to open directory ' . $source, E_USER_WARNING);
            throw new Exception('unabletocopy');
        }

        while (($file = readdir($dir)) !== false) {
            Log::info('file: ' . json_encode($file));
            if (($file != '.') && ($file != '..') && $file != '.git' && $file != '.gitignore' && !in_array($file, $ignoredFiles)) {
                if (is_dir("{$source}/{$file}")) {
                    Log::info('is_dir source/file');
                    Log::info('$source: ' . $source);
                    Log::info('$file: ' . $file);
                    Log::info('$destination: ' . $destination);
                    $this->copyFiles("{$source}/{$file}", "{$destination}/{$file}");
                }
                else {
                    Log::info('else w copyFiles');
                    Log::info('source: ' . "{$source}/{$file}");
                    Log::info('destination: ' . "{$destination}/{$file}");
                    // TODO może to inaczej działa niż copy
//                    $tmp = Storage::get("{$source}/{$file}");
//                    Storage::put("{$destination}/{$file}", $tmp);
                    $folder = Str::after($destination, env('AWS_URL'));
                    Storage::putFileAs($folder, new File("{$source}/{$file}"), $file);
//                    Storage::copy("{$source}/{$file}", "{$destination}/{$file}");
//                    copy("{$source}/{$file}", "{$destination}/{$file}");
//                    assert(Storage::fileExists($file));
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

        Log::info('ignoredFilesProvider before file_get_contents');
        $contents = file_get_contents($file);
        if ($contents === false) {
            return [];
        }

        return preg_split('/\s+/', $contents);
    }

    private function isDirReady($path): bool
    {
        Log::info('isDirReady: ' . $path);
//        if (!file_exists($path)) {
        if (!Storage::exists($path)) {
            Log::info('!file_exists($path): ' . $path);
            $parent = preg_replace("/\/[^\/]+\/?$/", '', $path);
            Log::info('parent: ' . $parent);
//            if ($parent !== 'http:/' && !$this->isDirReady($parent)) {
//            if ($parent !== env('AWS_URL') && !$this->isDirReady($parent)) {
//                Log::log('!$this->isDirReady($parent)');
//                return false;
//            }
            Log::info('mkdir: ' . json_encode($path));

            Storage::makeDirectory(Str::after($path, env('AWS_URL')));
//            mkdir($path, 0777, true);
        }

//        if (!is_dir($path)) {
        if (Storage::directoryExists($path)) {
            Log::info('!is_dir($path): ' . $path);
            trigger_error('Path is not a directory ' . $path, E_USER_WARNING);
            return false;
        }

//        if (!is_writable($path)) {
//            Log::info('!is_writable($path)');
//            trigger_error('Unable to write to ' . $path . ' – check directory permissions –', E_USER_WARNING);
//            return false;
//        }

        return true;
    }

//    private function isDirReady($path): bool
//    {
//        Log::info('isDirReady: ' . $path);
////        if (!file_exists($path)) {
//        if (!Storage::exists($path)) {
//            Log::info('!file_exists($path): ' . $path);
//            $parent = preg_replace("/\/[^\/]+\/?$/", '', $path);
//            Log::info('parent: ' . $parent);
////            if ($parent !== 'http:/' && !$this->isDirReady($parent)) {
//            if ($parent !== env('AWS_URL') && !$this->isDirReady($parent)) {
//                Log::log('!$this->isDirReady($parent)');
//                return false;
//            }
//            Log::info('mkdir: ' . json_encode($path));
//
//            Storage::makeDirectory(Str::after($path, env('AWS_URL')));
////            mkdir($path, 0777, true);
//        }
//
////        if (!is_dir($path)) {
//        if (!Storage::directoryExists($path)) {
//            Log::info('!is_dir($path): ' . $path);
//            trigger_error('Path is not a directory ' . $path, E_USER_WARNING);
//            return false;
//        }
//
////        if (!is_writable($path)) {
////            Log::info('!is_writable($path)');
////            trigger_error('Unable to write to ' . $path . ' – check directory permissions –', E_USER_WARNING);
////            return false;
////        }
//
//        return true;
//    }

    public function cacheAssets(&$files, $key)
    {
        foreach ($files as $type => $assets) {
            if (empty($assets)) {
                continue; // Skip no assets
            }

            $content = '';
            foreach ($assets as $asset) {
                // Get content from asset file
                // TODO zostaw to na razie, problem jest że się odpowiednie pliki nie zapisują na s3
//                dd($asset->path, $this->path, '/app/h5p' . $asset->path);
                $assetContent = Storage::get(config('hh5p.url') . $asset->path);
//                dd($tmp !== null);

//                $assetContent = file_get_contents($this->path . $asset->path);
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
                                Log::info('$cssRelPath: ' . $cssRelPath);
                                Log::info('$matches[1]: ' . $matches[1]);
                                Log::info('url("../' . $cssRelPath . $matches[1] . '")');
                                return 'url("../' . $cssRelPath . $matches[1] . '")';
                            },
                            $assetContent) . "\n";
                }
            }

            $this->isDirReady("{$this->path}/cachedassets");
            $ext = ($type === 'scripts' ? 'js' : 'css');
            $outputfile = "/cachedassets/{$key}.{$ext}";
            // TODO tutaj trzeba podmienić
//            dd('/app/h5p/cachedassets/', "{$key}.{$ext}");
            Storage::put(config('hh5p.url') . $outputfile, $content);
//            dd(Storage::path('/app/h5p' . $outputfile));
//            Storage::putFileAs('/app/h5p/cachedassets/', $content, "{$key}.{$ext}");
//            file_put_contents($this->path . $outputfile, $content);
            $files[$type] = array((object) array(
                'path' => $outputfile,
                'version' => ''
            ));
        }
    }

    public function saveFile($file, $contentId) {
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

        // Add filename to path
//        $path .= '/' . $file->getName();

//        copy($_FILES['file']['tmp_name'], $path);
//        dd($_FILES['file']['tmp_name'], $path);
//        dd($_FILES['file'], $file);
//        dd($_FILES['file']['tmp_name'], Str::after($path, env('AWS_URL')));
        Storage::putFileAs(Str::after($path, env('AWS_URL')), $_FILES['file']['tmp_name'], $file->getName());
//        Storage::copy($_FILES['file']['tmp_name'], Str::after($path, env('AWS_URL')));

        return $file;
    }

    private function getEditorPath()
    {
        return ($this->altEditorPath !== NULL ? $this->altEditorPath : "{$this->path}/editor");
    }
}
