<?php

namespace EscolaLms\HeadlessH5P\Traits;

use Illuminate\Http\Resources\Json\JsonResource;

trait ResourceExtendable
{
    private static array $extensions = [];

    public static function extend(callable $extension): void
    {
        self::$extensions[] = $extension;
    }

    public static function apply(array $fields, JsonResource $thisObj): array
    {
        foreach (self::$extensions as $extension) {
            $fields += $extension($thisObj);
        }
        return $fields;
    }
}
