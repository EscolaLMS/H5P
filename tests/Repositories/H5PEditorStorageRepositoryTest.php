<?php

namespace EscolaLms\HeadlessH5P\Tests\Repositories;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryLanguage;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorStorageRepository;
use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Tests\Stubs\StubH5PEditorFile;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class H5PEditorStorageRepositoryTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private H5PEditorStorageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(H5PEditorStorageRepository::class);
    }

    public function testShouldGetLanguageByCode(): void
    {
        $h5pLibrary = H5PLibrary::factory()->create([
            'major_version' => '1',
            'minor_version' => '1',
            'name' => 'H5P.Test',
        ]);

        H5PLibraryLanguage::factory()->languageCode('de')->create(['library_id' => $h5pLibrary->getKey()]);

        $result = $this->repository->getLanguage('H5P.Test', 1, 1, 'de');

        $this->assertStringContainsString('"code":"de"', $result);
        $this->assertStringContainsString('"code":"de"', $result);
    }

    public function testShouldReturnEmptyStringWhenTranslationNotExists(): void
    {
        H5PLibrary::factory()->create([
            'major_version' => '1',
            'minor_version' => '1',
            'name' => 'H5P.Test',
        ]);

        $result = $this->repository->getLanguage('H5P.Test', 1, 1, 'de');
        $this->assertEquals('', $result);
    }

    public function testShouldReturnEmptyStringWhenLanguageNotPass(): void
    {
        H5PLibrary::factory()->create([
            'major_version' => '1',
            'minor_version' => '1',
            'name' => 'H5P.Test',
        ]);

        $result = $this->repository->getLanguage('H5P.Test', 1, 1, null);
        $this->assertEquals('', $result);
    }

    public function testShouldGetListAvailableLanguages(): void
    {
        $h5pLibrary = H5PLibrary::factory()->create([
            'major_version' => '1',
            'minor_version' => '1',
            'name' => 'H5P.Test',
        ]);
        H5PLibraryLanguage::factory()->languageCode('pl')->create(['library_id' => $h5pLibrary->getKey()]);
        H5PLibraryLanguage::factory()->languageCode('fr')->create(['library_id' => $h5pLibrary->getKey()]);

        $result = $this->repository->getAvailableLanguages('H5P.Test', 1, 1);

        $this->assertContains('pl', $result);
        $this->assertContains('fr', $result);
        $this->assertContains('en', $result);
        $this->assertNotContains('de', $result);
        $this->assertCount(3, $result);
    }

    public function testShouldGetOnlyDefaultLangInAvailableLanguages(): void
    {
        H5PLibrary::factory()->create([
            'major_version' => '1',
            'minor_version' => '1',
            'name' => 'H5P.Test',
        ]);

        $result = $this->repository->getAvailableLanguages('H5P.Test', 1, 1);

        $this->assertCount(1, $result);
        $this->assertNotContains('eu', $result);
    }

    public function testShouldGetLibrariesList(): void
    {
        $libraries[] = H5PLibrary::factory()->create([
            'major_version' => '1',
            'minor_version' => '1',
            'name' => 'H5P.Test1',
        ]);
        $libraries[] = H5PLibrary::factory()->create([
            'major_version' => '1',
            'minor_version' => '1',
            'name' => 'H5P.Test2',
        ]);
        $stub = H5PLibrary::factory()->count(5)->create();

        $result = $this->repository->getLibraries($libraries);

        $this->assertCount(2, $result);
        $this->assertContains($libraries[0]->getKey(), array_map(fn($item) => $item->getKey(), $result));
        $this->assertContains($libraries[1]->getKey(), array_map(fn($item) => $item->getKey(), $result));
        $this->assertNotContains($stub[0]->getKey(), array_map(fn($item) => $item->getKey(), $result));
    }

    public function testShouldGetRunnableLibrariesWhenPassedValueIsEmpty(): void
    {
        $runnable = H5PLibrary::factory()->count(5)->create(['runnable' => 1]);
        $other = H5PLibrary::factory()->count(2)->create(['runnable' => 0]);

        $result = $this->repository->getLibraries();

        $this->assertCount(5, $result);
        $this->assertContains($runnable[0]->getKey(), array_map(fn($item) => $item->getKey(), $result));
        $this->assertNotContains($other[0]->getKey(), array_map(fn($item) => $item->getKey(), $result));
    }

    public function testShouldCreateFileForCleanup(): void
    {
        H5PContent::factory()
            ->count(5)
            ->create(['library_id' => H5PLibrary::factory()->create()->getKey()]);
        $content = H5PContent::factory()->create([
            'nonce' => 'nonce123',
            'library_id' => H5PLibrary::factory()->create()->getKey()
        ]);

        $file = new StubH5PEditorFile(app(H5PRepository::class));
        $file->name = 'audio.mp3';
        $file->type = 'audio';

        $result = $this->repository::markFileForCleanup($file, $content->nonce);

        $this->assertDatabaseHas('hh5p_temp_files', [
            'nonce' => 'nonce123',
            'path' => '/content/' . $content->getKey() . '/audios/audio.mp3'
        ]);
        $this->assertEquals($content->nonce, $result->content->nonce);
    }

    public function testShouldNotCreateFileForCleanup(): void
    {
        $file = new StubH5PEditorFile(app(H5PRepository::class));
        $file->name = 'audio.mp3';
        $file->type = 'audio';

        $this->repository::markFileForCleanup($file, 'nonce123');

        $this->assertDatabaseMissing('hh5p_temp_files', [
            'nonce' => 'nonce123',
            'path' => '/content/123/audios/audio.mp3'
        ]);
    }

    public function testRemoveTemporarySavedFiles(): void
    {
        Storage::fake();
        UploadedFile::fake()->create('audio.mp3')->storeAs('/contents/123', 'audio.mp3');

        Storage::assertExists('/contents/123/audio.mp3');

        $this->repository::removeTemporarilySavedFiles('contents/123/audio.mp3');

        Storage::assertMissing('/contents/123/audio.mp3');
    }

    public function testShouldCleanMultipleFiles(): void
    {
        Storage::fake();
        UploadedFile::fake()->create('audio1.mp3')->storeAs('/contents/123', 'audio1.mp3');
        UploadedFile::fake()->create('audio2.mp3')->storeAs('/contents/123', 'audio2.mp3');
        UploadedFile::fake()->create('audio3.mp3')->storeAs('/contents/123', 'audio3.mp3');

        Storage::assertExists('/contents/123/audio1.mp3');
        Storage::assertExists('/contents/123/audio2.mp3');
        Storage::assertExists('/contents/123/audio3.mp3');

        $this->repository::removeTemporarilySavedFiles('contents/123');

        Storage::assertMissing('/contents/123/audio1.mp3');
        Storage::assertMissing('/contents/123/audio2.mp3');
        Storage::assertMissing('/contents/123/audio3.mp3');
    }

    public function testShouldCleanFileTree(): void
    {
        Storage::fake();
        UploadedFile::fake()->create('audio.mp3')->storeAs('/contents/123/audio', 'audio.mp3');
        UploadedFile::fake()->create('video.mp4')->storeAs('/contents/123/video/mp4', 'video.mp4');
        UploadedFile::fake()->create('text.txt')->storeAs('/contents/123/', 'text.txt');

        Storage::assertExists('/contents/123/audio/audio.mp3');
        Storage::assertExists('/contents/123/video/mp4/video.mp4');
        Storage::assertExists('/contents/123/text.txt');

        $this->repository::removeTemporarilySavedFiles('contents/123');

        Storage::assertMissing('/contents/123/audio/audio.mp3');
        Storage::assertMissing('/contents/123/video/mp4/video.mp4');
        Storage::assertMissing('/contents/123/text.txt');
    }
}
