<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LibraryLanguageApiTest extends TestCase
{
    use H5PTestingTrait, DatabaseTransactions;

    public function testUploadLibraryShouldCreateTranslationFromLocalRepo(): void
    {
        $this->authenticateAsAdmin();
        $h5pFile = $this->getH5PFile('image-juxtaposition.h5p');
        $this->actingAs($this->user, 'api')
            ->postJson('/api/admin/hh5p/library', ['h5p_file' => $h5pFile,])
            ->assertStatus(200);

        $libraryName = 'H5P.ImageJuxtaposition';
        $library = H5PLibrary::where('name', '=', $libraryName)->first();
        $this->assertDatabaseHas('hh5p_libraries', [
            'name' => $libraryName
        ]);
        $this->assertDatabaseHas('hh5p_libraries_languages', [
            'library_id' => $library->getKey(),
            'language_code' => 'pl',
        ]);
    }

    public function testContentUploadLibraryShouldCreateTranslationFromLocalRepo(): void
    {
        $this->authenticateAsAdmin();
        $h5pFile = $this->getH5PFile('image-juxtaposition.h5p');
        $this->actingAs($this->user, 'api')
            ->postJson('/api/admin/hh5p/content/upload', ['h5p_file' => $h5pFile,])
            ->assertStatus(200);

        $libraryName = 'H5P.ImageJuxtaposition';
        $library = H5PLibrary::where('name', '=', $libraryName)->first();
        $this->assertDatabaseHas('hh5p_libraries', [
            'name' => $libraryName
        ]);
        $this->assertDatabaseHas('hh5p_libraries_languages', [
            'library_id' => $library->getKey(),
            'language_code' => 'pl',
        ]);
    }

    public function testGetEditorLibraryShouldUpdateTranslationFromLocalRepo(): void
    {
        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $library = H5PLibrary::create([
            'name' => 'H5P.ImageJuxtaposition',
            'title' => 'Image Juxtaposition',
            'major_version' => 1,
            'minor_version' => 4,
            'patch_version' => 1,
            'runnable' => 1,
            'fullscreen' => 0,
            'embed_types' => 'iframe',
            'preloaded_js' => 'dist/h5p-image-juxtaposition.js',
            'preloaded_css' => 'dist/h5p-image-juxtaposition.css',
            'drop_library_css' => '',
            'semantics' => '[]',
            'tutorial_url' => '',
            'has_icon' => 1,
            'add_to' => null
        ]);
        $this->assertDatabaseMissing('hh5p_libraries_languages', [
            'library_id' => $library->getKey(),
            'language_code' => 'pl',
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/hh5p/libraries?_token=' . $token . '&machineName=' . $library->machineName . '&majorVersion=' . $library->majorVersion . '&minorVersion=' . $library->minorVersion . '&lang=pl')
            ->assertStatus(200);
        $this->assertDatabaseHas('hh5p_libraries_languages', [
            'library_id' => $library->getKey(),
            'language_code' => 'pl',
        ]);
    }
}
