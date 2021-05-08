<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

class LibraryApiTest extends TestCase
{
    public function test_library_uploadig()
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->post('/api/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        if ($response->status() >= 422) {
            echo $response->content();
        }

        $response->assertStatus(200);
    }

    public function test_library_index()
    {
        $response = $this->get('/api/hh5p/library');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'id', 'machineName', 'majorVersion', 'minorVersion'
            ]
        ]);
    }

    public function test_library_delete()
    {
        $library = H5PLibrary::where('runnable', 1)->first();
        $id = $library->id;

        $response = $this->delete("/api/hh5p/library/$id");
        $response->assertStatus(200);

        $response = $this->delete("/api/hh5p/library/$id");
        $response->assertStatus(404);
    }
}
