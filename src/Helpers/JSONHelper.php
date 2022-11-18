<?php

namespace EscolaLms\HeadlessH5P\Helpers;

class JSONHelper
{
    public static function clearStr(array $chars, string $to, string $jsonString): string
    {
        return str_replace($chars, $to, $jsonString);
    }

    public static function clearObj(array $chars, string $to, object $json): string
    {
        return str_replace($chars, $to, json_encode($json));
    }

    public static function compareStr(string $jsonString1, string $jsonString2): bool
    {
        return json_encode(json_decode($jsonString1)) === json_encode(json_decode($jsonString2));
    }

    public static function compareObj(object $json1, object $json2): bool
    {
        return json_encode($json1) === json_encode($json2);
    }

    public static function compareArr(array $json1, array $json2): bool
    {
        return json_encode($json1) === json_encode($json2);
    }
}
