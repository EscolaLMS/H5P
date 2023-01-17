<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PContentLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Http\Middleware\QueryToken;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\HeadlessH5P\Tests\Stubs\StubHeadlessH5PService;
use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Psr7\Response;


class LibraryApiTest extends TestCase
{
    use DatabaseTransactions, H5PTestingTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock->reset();

        Route::middleware([QueryToken::class, 'auth:api'])
            ->group(__DIR__ . './../../src/routes.php');
    }

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
                'minorVersion',
                'contentsCount',
                'requiredLibrariesCount'
            ]]
        ]);
    }

    public function testGuestCannotDeleteLibrary(): void
    {
        $library = H5PLibrary::factory()->create();
        $id = $library->id;

        $response = $this->deleteJson("/api/admin/hh5p/library/$id");

        $response->assertUnauthorized();
    }

    public function testGuestCannotIndexLibrary(): void
    {
        $this->getJson('/api/admin/hh5p/library')->assertUnauthorized();
    }

    public function testGuestCannotUploadLibrary(): void
    {
        $h5pFile = $this->getH5PFile();

        $response = $this->postJson('/api/admin/hh5p/library', [
            'h5p_file' => $h5pFile,
        ]);

        $response->assertUnauthorized();
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

    public function testContentTypeCache(): void
    {
        $this->mock->append(new Response(200, [], json_encode(["uuid" => "123"])));
        $this->mock->append(new Response(200, [], json_encode(["uuid" => "123"])));

        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $this
            ->getJson('/api/hh5p/content-type-cache?_token=' . $token)
            ->assertOk()
            ->assertJsonStructure([
                'apiVersion' => ['major', 'minor'],
                'details',
                'libraries',
                'outdated',
                'recentlyUsed',
            ]);
    }

    public function testContentTypeCacheUnauthorized(): void
    {
        $this->getJson('/api/hh5p/content-type-cache')
            ->assertUnauthorized();
    }

    public function testFilterLibraryParameters(): void
    {
        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;
        $h5pContent = $this->uploadHP5Content();

        $params = json_encode([
            'params' => $h5pContent->params,
            'metadata' => $h5pContent->metadata,
            'library' => $h5pContent->library->uberName
        ]);

        $this
            ->postJson('/api/hh5p/filter?libraryParameters=' . $params . '&_token=' . $token)
            ->assertOk();
    }

    public function testFilterLibraryParametersInvalidData(): void
    {
        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;

        $this
            ->postJson('/api/hh5p/filter?_token=' . $token)
            ->assertJson([
                'message' => 'Could not parse post data.',
            ])
            ->assertUnprocessable();
    }

    public function testFilterLibraryParametersUnauthorized(): void
    {
        $this
            ->postJson('/api/hh5p/filter')
            ->assertUnauthorized();
    }

    public function testHubContentHubMetadataCache(): void
    {
        $this->authenticateAsAdmin();
        $token = $this->user->createToken("test")->accessToken;
        $this->app->singleton(HeadlessH5PServiceContract::class, StubHeadlessH5PService::instance());

        $this
            ->getJson('/api/hh5p/content-hub-metadata-cache?_token=' . $token)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'disciplines',
                    'languages',
                    'levels',
                    'licenses',
                ],
                'success'
            ]);
    }

    public function testHubContentHubMetadataCacheUnauthorized(): void
    {
        $this
            ->getJson('/api/hh5p/content-hub-metadata-cache')
            ->assertUnauthorized();
    }

    public function testGetLibrariesAdmin(): void
    {
        $this->authenticateAsAdmin();

        $lib1 = H5PLibrary::factory()->create();
        $lib2 = H5PLibrary::factory()->create();
        H5PLibraryDependency::factory()->count(3)->create(['required_library_id' => $lib1->getKey()]);
        H5PLibraryDependency::factory()->count(7)->create(['required_library_id' => $lib2->getKey()]);
        H5PContent::factory()->count(2)->create(['library_id' => $lib1->getKey()]);
        H5PContent::factory()->count(5)->create(['library_id' => $lib2->getKey()]);

        $response = $this
            ->actingAs($this->user, 'api')
            ->getJson('api/admin/hh5p/libraries')
            ->assertOk();

        $data = $response->getData();
        $this->assertEquals(2, current(array_filter($data, fn($item) => $item->id === $lib1->getKey()))->contentsCount);
        $this->assertEquals(5, current(array_filter($data, fn($item) => $item->id === $lib2->getKey()))->contentsCount);
        $this->assertEquals(3, current(array_filter($data, fn($item) => $item->id === $lib1->getKey()))->requiredLibrariesCount);
        $this->assertEquals(7, current(array_filter($data, fn($item) => $item->id === $lib2->getKey()))->requiredLibrariesCount);
    }

    public function test_reinstall_library_dependencies()
    {
        $this->authenticateAsAdmin();

        $h5pFile = $this->getH5PFile();
        $this->uploadH5PLibrary($h5pFile);

        $library = H5PLibrary::first();
        $libraryDependencies = H5PLibraryDependency::where('library_id', $library->getKey());
        $libraryDependenciesCount = $libraryDependencies->count();

        $libraryDependencies->first()->delete();

        $this->assertNotEquals($libraryDependenciesCount, $libraryDependencies->count());

        $this->mock->append(new Response(200, ['Content-Type' => 'application/json']));

        $this
            ->actingAs($this->user, 'api')
            ->postJson('api/hh5p/library-reinstall-dependencies?id=H5P.ArithmeticQuiz')
            ->assertOk();

        $this->assertEquals($libraryDependenciesCount, H5PLibraryDependency::where('library_id', $library->getKey())->count());
    }
}
