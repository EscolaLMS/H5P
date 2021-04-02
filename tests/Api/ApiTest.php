<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryApiTest extends TestCase
{
    public function test_uploadig_library()
    {
        //Storage::fake('avatars');

        $h5pFile = new UploadedFile(__DIR__.'/../mocks/arithmetic-quiz.h5p', 'arithmetic-quiz.h5p');

        $response = $this->post('/api/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertStatus(200);

        // TODO additional tests

        // Files are created, more then 10 folders

        // Database is filled,  more then 10 records
    }
}
