<?php

namespace EscolaLms\HeadlessH5P\Tests\Helpers;

use EscolaLms\HeadlessH5P\Helpers\MargeFiles;
use EscolaLms\HeadlessH5P\Tests\TestCase;

class MargeFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testMargeFiles()
    {
        $firstFileName = __DIR__.'/MargeFilesTest.php';
        $secondFileName =  __DIR__.'/../TestCase.php';
        $arr = [
            $firstFileName,
        ];
        $margeFiles = new MargeFiles();
        $margeFiles->setFilesArray($arr);
        $margeFiles->addFile($secondFileName);
        $margeFiles->setFileType('css');

        $fileName = $margeFiles->getHashedFile();

        $this->assertFileExists($fileName);

        $fileContent = file_get_contents($fileName);
        $firstContent = file_get_contents($firstFileName);
        $secondContent = file_get_contents($secondFileName);

        $this->assertStringContainsString($firstContent, $fileContent);
        $this->assertStringContainsString($secondContent, $fileContent);
    }
}
