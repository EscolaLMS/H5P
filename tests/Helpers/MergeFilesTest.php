<?php

namespace EscolaLms\HeadlessH5P\Tests\Helpers;

use EscolaLms\HeadlessH5P\Helpers\MergeFiles;
use EscolaLms\HeadlessH5P\Tests\TestCase;

class MergeFilesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testMergeFiles()
    {
        $firstFileName = __DIR__ . '/MergeFilesTest.php';
        $secondFileName =  __DIR__ . '/../TestCase.php';
        $arr = [
            $firstFileName,
        ];
        $mergeFiles = new MergeFiles();
        $mergeFiles->setFilesArray($arr);
        $mergeFiles->addFile($secondFileName);
        $mergeFiles->setFileType('css');

        $fileName = $mergeFiles->getHashedFile();

        $this->assertFileExists($fileName);

        $fileContent = file_get_contents($fileName);
        $firstContent = file_get_contents($firstFileName);
        $secondContent = file_get_contents($secondFileName);

        $this->assertStringContainsString($firstContent, $fileContent);
        $this->assertStringContainsString($secondContent, $fileContent);
    }
}
