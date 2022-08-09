<?php

namespace EscolaLms\HeadlessH5P\Tests\Stubs;

use H5peditorFile;

class StubH5PEditorFile extends H5peditorFile
{
    public function getType()
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
