<?php

namespace EscolaLms\HeadlessH5P\Tests\Stubs;

use H5PCore;

class StubH5PCore extends H5PCore
{
    // in tests this method not exists
    public function getUpdatedContentHubMetadataCache($lang = 'en') {
        return [
            // TODO
        ];
    }
}
