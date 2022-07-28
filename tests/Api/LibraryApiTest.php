<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;

class LibraryApiTest extends TestCase
{
    use DatabaseTransactions, H5PTestingTrait;

    public function test_library_uploading(): void
    {
        $this->authenticateAsAdmin();
        $h5pFile = $this->getH5PFile();
        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/hh5p/library', [
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
        H5PLibrary::factory()->create();

        $response = $this->actingAs($this->user, 'api')->getJson('/api/admin/hh5p/library');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [[
                'id',
                'machineName',
                'majorVersion',
                'minorVersion'
            ]]
        ]);
    }

    public function test_library_delete(): void
    {
        $this->authenticateAsAdmin();
        $library = H5PLibrary::factory()->create();
        $id = $library->id;

        $response = $this->actingAs($this->user, 'api')->deleteJson("/api/admin/hh5p/library/$id");
        $response->assertStatus(200);

        $response = $this->actingAs($this->user, 'api')->deleteJson("/api/admin/hh5p/library/$id");
        $response->assertStatus(404);
    }

    public function testGuestCannotDeleteLibrary(): void
    {
        $library = H5PLibrary::factory()->create();
        $id = $library->id;

        $response = $this->deleteJson("/api/admin/hh5p/library/$id");

        $response->assertForbidden();
    }

    public function testGuestCanIndexLibrary(): void
    {
        $response = $this->getJson('/api/admin/hh5p/library');

        $response->assertOk();
    }

    public function testGuestCannotUploadLibrary(): void
    {
        $h5pFile = $this->getH5PFile();

        $response = $this->postJson('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertForbidden();
    }
}
