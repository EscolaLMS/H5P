<?php

namespace EscolaLms\HeadlessH5P\Helpers;

use Exception;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MargeFiles
{
    private array $arrayFiles;
    private string $hash;
    private string $patch;
    private string $fileType;

    public function __construct(?array $filesArray = null, ?string $fileType = null, ?string $patch = null)
    {
        $this->arrayFiles = $filesArray ?? [];
        $this->fileType = $fileType ?? 'txt';
        $this->patch = $patch ?? storage_path();
        $this->hash = $this->getHash();
    }

    public function setFileType(string $fileType): void
    {
        $this->fileType = $fileType;
    }

    public function setFilesArray(array $filesArray): void
    {
        $this->arrayFiles = $filesArray;
    }

    public function addFile(string $file): void
    {
        $this->arrayFiles[] = $file;
    }

    public function setPatch(string $patch): void
    {
        $this->patch = $patch;
    }

    public function getHash(): string
    {
        $hash = [];
        foreach ($this->arrayFiles as $file) {
            $hash[] = hash('md5', Storage::get($this->getNameAfterPrefix($file)));
        }

        return md5(serialize($hash));
    }

    /**
     * @throws Exception
     */
    public function getHashedFile(): string
    {
        $this->hash = $this->getHash();
        $fileName = $this->getFileName();
        if (!file_exists($fileName)) {
            if (!$this->createFile($fileName)) {
                throw new Exception("Did not create file");
            }
        }

        return $fileName;
    }

    public function getFileName(): string
    {
        return $this->generateStoragePath() . '/' . ($this->hash ?? 'temp') . '.' . $this->fileType;
    }

    private function generateStoragePath(): string
    {
        $storagePath = $this->patch . '/' . $this->fileType;

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        return $storagePath;
    }

    /**
     * @throws Exception
     */
    private function createFile(string $fileName): bool
    {
        if (!is_readable($fileName) && $this->arrayFiles) {
            $stream = fopen('php://memory', 'r+');
            foreach ($this->arrayFiles as $path) {
                $contents = $this->getContent($path);
                fwrite($stream, $contents . PHP_EOL);
            }
            rewind($stream);
            Storage::put($this->getNameAfterPrefix($fileName), $stream);
            fclose($stream);

            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function getContent(string $path): string
    {
        $folderPath = $this->getNameAfterPrefix($path);
        if (Storage::exists($folderPath)) {
            return Storage::get($folderPath);
        }

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        throw new Exception("File: '".$path."' do not exist");
    }

    private function getNameAfterPrefix(string $fileName): string
    {
        return env('AWS_URL', null) ? Str::after($fileName, env('AWS_URL')) : $fileName;
    }
}
