<?php

namespace EscolaLms\HeadlessH5P\Tests\Stubs;

use H5PCore;

class StubH5PCore extends H5PCore
{
    // in tests this method not exists
    // throw Call to undefined function H5PCore::getUpdatedContentHubMetadataCache
    public function getUpdatedContentHubMetadataCache($lang = 'en') {
        return [
            'disciplines' => [],
            'languages' => [],
            'levels' => [],
            'licenses' => [],
        ];
    }
}
