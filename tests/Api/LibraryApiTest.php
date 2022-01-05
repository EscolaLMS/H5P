<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

class LibraryApiTest extends TestCase
{
    public function test_library_uploading(): void
    {
        $this->authenticateAsAdmin();
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->actingAs($this->user, 'api')->post('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        if ($response->status() >= 422) {
            echo $response->content();
        }

        $response->assertStatus(200);
    }

    public function test_library_index(): void
    {
        $this->authenticateAsAdmin();
        $response = $this->actingAs($this->user, 'api')->get('/api/admin/hh5p/library');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                [
                    'id', 'machineName', 'majorVersion', 'minorVersion'
                ]
            ]
        ]);
    }

    public function test_library_delete(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::where('runnable', 1)->first();
        $id = $library->id;

        $response = $this->actingAs($this->user, 'api')->delete("/api/admin/hh5p/library/$id");
        $response->assertStatus(200);

        $response = $this->actingAs($this->user, 'api')->delete("/api/admin/hh5p/library/$id");
        $response->assertStatus(404);
    }

    public function testGuestCannotDeleteLibrary(): void
    {
        $library = H5PLibrary::first();
        $id = $library->id;

        $response = $this->delete("/api/admin/hh5p/library/$id");

        $response->assertForbidden();
    }

    public function testGuestCannotIndexLibrary(): void
    {
        $response = $this->get('/api/admin/hh5p/library');

        $response->assertForbidden();
    }

    public function testGuestCannotUploadLibrary(): void
    {
        $filename = 'arithmetic-quiz.h5p';
        $filepath = realpath(__DIR__.'/../mocks/'.$filename);
        $storage_path = storage_path($filename);

        copy($filepath, $storage_path);

        $h5pFile = new UploadedFile($storage_path, 'arithmetic-quiz.h5p', 'application/pdf', null, true);

        $response = $this->post('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertForbidden();
    }
}
