<?php

namespace EscolaLms\HeadlessH5P\Helpers;

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
}
