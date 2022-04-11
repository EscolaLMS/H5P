<?php

namespace EscolaLms\HeadlessH5P\Tests\Traits;

use Illuminate\Http\UploadedFile;

trait H5PTestingTrait
{
    protected function uploadH5PFile(): array
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);
        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);
        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/content/upload', [
            'h5p_file' => $h5pFile,
        ]);

        return $response->json('data');
    }
}
