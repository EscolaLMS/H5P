<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PContentLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;
use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
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

    public function testGuestCannotDeleteLibrary(): void
    {
        $library = H5PLibrary::factory()->create();
        $id = $library->id;

        $response = $this->deleteJson("/api/admin/hh5p/library/$id");

        $response->assertForbidden();
    }

    public function testGuestCannotIndexLibrary(): void
    {
        $this->getJson('/api/admin/hh5p/library')->assertForbidden();
    }

    public function testGuestCannotUploadLibrary(): void
    {
        $h5pFile = $this->getH5PFile();

        $response = $this->postJson('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertForbidden();
    }

    public function testLibraryDestroyShouldNotDestroyWhenLibraryIsInUsage(): void
    {
        $this->authenticateAsAdmin();
        $this->uploadH5PLibrary();
        $library = H5PLibrary::whereRunnable(0)->first();

        H5PLibrary::factory()
            ->count(3)
            ->has(H5PLibraryDependency::factory()->state(['required_library_id' => $library->getKey()]), 'dependencies')
            ->create();
        H5PContent::factory()
            ->count(5)
            ->has(H5PContentLibrary::factory()->state(['library_id' => $library->getKey()]), 'libraries')
            ->create(['library_id' => $library->getKey()]);

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/admin/hh5p/library/' . $library->getKey())
            ->assertUnprocessable()
            ->assertJson(['message' => 'Library ' . $library->getKey() . ' note deleted']);
    }

    public function testLibraryDestroy(): void
    {
        $this->authenticateAsAdmin();
        $this->uploadH5PLibrary();
        $library = H5PLibrary::whereRunnable(0)->first();
        H5PContentLibrary::query()->delete();
        H5PLibraryDependency::query()->delete();

        $this->actingAs($this->user, 'api')
            ->deleteJson('/api/admin/hh5p/library/' . $library->getKey())
            ->assertOk();

        $this->actingAs($this->user, 'api')->deleteJson('/api/admin/hh5p/library/' .  $library->getKey())
            ->assertNotFound();
    }
}
