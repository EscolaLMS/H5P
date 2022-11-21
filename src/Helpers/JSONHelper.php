<?php

namespace EscolaLms\HeadlessH5P\Helpers;

class JSONHelper
{
    public static function clearJson($json): string
    {
        if (empty($json)) {
            return '';
        }

        if (is_object($json) || is_array($json)) {
            $json = json_encode($json);
        }

        $json = str_replace(['\n', '\t'], '', $json);
        $json = str_replace(['\"', '&quot;'], '\'', $json);
        return str_replace(['\/'], '/', $json);
    }

    public static function compareStr(string $jsonString1, string $jsonString2): bool
    {
        return json_encode(json_decode($jsonString1)) === json_encode(json_decode($jsonString2));
    }

    public static function compareArr(array $json1, array $json2): bool
    {
        return json_encode($json1) === json_encode($json2);
    }
}
