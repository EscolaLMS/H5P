<?php

namespace EscolaLms\HeadlessH5P\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Helpers
{
    /**
     * fix case key arrays for mysql/postges/other case sensitive results
     */
    public static function fixCaseKeysArray($keys, $array)
    {
        if (is_object($array)) {
            $row = $array;
            foreach ($keys as $key) {
                $lckey = strtolower($key);
                if (is_array($row) && !isset($row[$key]) && isset($row[$lckey])) {
                    $row[$key] = $row[$lckey];
                }
                if (is_object($row) && !isset($row->$key) && isset($row->$lckey)) {
                    $row->$key = $row->$lckey;
                }
            }
        } else {
            foreach ($array as $row_key => $row) {
                foreach ($keys as $key) {
                    $lckey = strtolower($key);
                    if (is_array($row) && !isset($row[$key]) && isset($row[$lckey])) {
                        $row[$key] = $row[$lckey];
                    }
                    if (is_object($row) && !isset($row->$key) && isset($row->$lckey)) {
                        $row->$key = $row->$lckey;
                    }
                }
            }
        }
    }

    /**
     * Recursive function for removing directories.
     *
     * @param string $dir
     *  Path to the directory we'll be deleting
     * @return boolean
     *  Indicates if the directory existed.
     */
    public static function deleteFileTree($dir)
    {
        if (!Storage::directoryExists($dir)) {
            return false;
        }
        // TODO to remove ??
        if (is_link($dir)) {
            // Do not traverse and delete linked content, simply unlink.
            unlink($dir);
            return;
        }

        foreach (Storage::directories($dir) as $directory) {
            self::deleteFileTree($directory);
        }
        foreach (Storage::files($dir) as $file) {
            Storage::delete($file);
        }

        return Storage::deleteDirectory($dir);
    }
}
