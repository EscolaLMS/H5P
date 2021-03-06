<?php

namespace EscolaLms\HeadlessH5P\Tests\Traits;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use Illuminate\Http\UploadedFile;

trait H5PTestingTrait
{
    protected function getH5PFile(): UploadedFile
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);
        copy($filepath, $storage_path);

        return new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);
    }

    protected function uploadHP5Content(): H5PContent
    {
        $repository = app(H5PContentRepositoryContract::class);
        return $repository->upload($this->getH5PFile());
    }

    protected function uploadH5PFile(): array
    {
        $h5pFile = $this->getH5PFile();
        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/content/upload', [
            'h5p_file' => $h5pFile,
        ]);

        return $response->json('data');
    }

    protected function uploadH5PLibrary(): void
    {
        $h5pFile = $this->getH5PFile();
        $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);
    }
}
