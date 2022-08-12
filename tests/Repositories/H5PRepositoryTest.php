<?php

namespace EscolaLms\HeadlessH5P\Tests\Repositories;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Models\H5PContentLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Models\H5PLibraryDependency;
use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Tests\Fixture\H5PContentTypeFixture;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use H5PHubEndpoints;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class H5PRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private H5PRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock->reset();
        $this->repository = app(H5PRepository::class);
    }

    public function testFetchExternalDataShouldReturnFullData(): void
    {
        $this->mock->append(new Response(200, [], json_encode(['var1' => 'val1'])));

        $url = H5PHubEndpoints::createURL('example/url');
        $result = $this->repository->fetchExternalData($url, null, false, null, true);

        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertSame(200, $result['status']);
        $this->assertSame('val1', $result['data']->var1);
    }

    public function testFetchExternalDataShouldReturnDataInStringJson(): void
    {
        $this->mock->append(new Response(200, [], json_encode(['var1' => 'val1'])));

        $url = H5PHubEndpoints::createURL('example/url');
        $result = $this->repository->fetchExternalData($url, ['param1' => 'value1'], false, null, false);

        $this->assertEquals('{"var1":"val1"}', $result);
    }

    public function testFetchExternalDataBlockingTrue(): void
    {
        $this->mock->append(new Response(200, [], json_encode(['var1' => 'val1'])));

        $url = H5PHubEndpoints::createURL('example/url');
        $result = $this->repository->fetchExternalData($url, null, true, null, false);

        $this->assertEquals('{"var1":"val1"}', $result);
    }

    public function testFetchExternalDataShouldReturnFalseWhenStatusIs400(): void
    {
        $this->mock->append(new Response(400, [], json_encode(['var1' => 'val1'])));

        $url = H5PHubEndpoints::createURL('example/url');
        $result = $this->repository->fetchExternalData($url, null, false, null, false);

        $this->assertFalse($result);
    }

    public function testFetchExternalDataShouldReturnFalseWhenStatusIsDifferentFrom200(): void
    {
        $this->mock->append(new Response(302, [], json_encode(['var1' => 'val1'])));

        $url = H5PHubEndpoints::createURL('example/url');
        $result = $this->repository->fetchExternalData($url, null, false, null, false);

        $this->assertFalse($result);
    }

    public function testLoadAddonsShouldReturnHigherVersionLibrary(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test', 'major_version' => 1, 'minor_version' => 2, 'add_to' => 'sth']);
        $library1 = H5PLibrary::factory()->create(['name' => 'Test', 'major_version' => 1, 'minor_version' => 3, 'add_to' => 'sth']);

        $result = $this->repository->loadAddons();

        $this->assertCount(1, $result);
        $this->assertEquals($library1->getKey(), $result[0]['id']);
        $this->assertEquals(3, $result[0]['minorVersion']);
        $this->assertEquals(1, $result[0]['majorVersion']);
    }

    public function testLoadAddonsShouldReturnHigherMajorVersionLibrary(): void
    {
        $library1 = H5PLibrary::factory()->create(['name' => 'Test', 'major_version' => 2, 'minor_version' => 1, 'add_to' => 'sth']);
        H5PLibrary::factory()->create(['name' => 'Test', 'major_version' => 1, 'minor_version' => 1, 'add_to' => 'sth']);

        $result = $this->repository->loadAddons();

        $this->assertCount(1, $result);
        $this->assertEquals($library1->getKey(), $result[0]['id']);
        $this->assertEquals(1, $result[0]['minorVersion']);
        $this->assertEquals(2, $result[0]['majorVersion']);
    }

    public function testLoadAddonsShouldReturnAllLibrariesWhenNamesIsNotSame(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 2, 'minor_version' => 1, 'add_to' => 'sth']);
        H5PLibrary::factory()->create(['name' => 'Test2', 'major_version' => 1, 'minor_version' => 3, 'add_to' => 'sth']);

        $result = $this->repository->loadAddons();

        $this->assertCount(2, $result);
    }

    public function testDeleteLibraryUsageShouldDeleteLibrariesById(): void
    {
        $h5pContent = H5PContent::factory()
            ->has(H5PContentLibrary::factory()->count(5), 'libraries')
            ->create(['library_id' => H5PLibrary::factory()->create()->getKey()]);

        $this->assertCount(5, $h5pContent->libraries);

        $this->repository->deleteLibraryUsage($h5pContent->getKey());

        $this->assertCount(0, H5PContent::find($h5pContent->getKey())->libraries);
    }

    public function testDeleteLibraryUsageShouldFailWhenContentNotExists(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->deleteLibraryUsage(1);
    }

    public function testDeleteLibraryDependenciesShouldDeleteAllDependencies(): void
    {
        $library = H5PLibrary::factory()
            ->has(H5PLibraryDependency::factory(), 'dependencies')
            ->create();

        $this->assertCount(1, $library->dependencies);

        $this->repository->deleteLibraryDependencies($library->getKey());

        $this->assertCount(0, H5PLibrary::find($library->getKey())->dependencies);
    }

    public function testDeleteLibraryDependenciesShouldFailWhenLibraryNotExists(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->deleteLibraryDependencies(123);
    }

    public function testUpdateContentFieldsShouldUpdateSlugAndFilteredFields(): void
    {
        $h5pContent = H5PContent::factory()->create(['library_id' => H5PLibrary::factory()->create()->getKey()]);

        $this->repository->updateContentFields($h5pContent->getKey(), ['slug' => 'slug-123', 'filtered' => '{"test": "test"}']);

        $result = H5PContent::find($h5pContent->getKey());
        $this->assertEquals('slug-123', $result->slug);
        $this->assertEquals('{"test": "test"}', $result->filtered);
        $this->assertNotEquals($h5pContent->slug, $result->slug);
        $this->assertNotEquals($h5pContent->filtered, $result->filtered);
    }

    public function testClearFilteredParametersShouldClearFilteredField(): void
    {
        $libraryId1 = H5PLibrary::factory()->create()->getKey();
        $libraryId2 = H5PLibrary::factory()->create()->getKey();
        $h5pContent1 = H5PContent::factory()->create(['library_id' => $libraryId1, 'filtered' => '{"foo": "bar"']);
        $h5pContent2 = H5PContent::factory()->create(['library_id' => $libraryId1, 'filtered' => '{"foo": "bar"']);
        $h5pContent3 = H5PContent::factory()->create(['library_id' => $libraryId2, 'filtered' => '{"foo": "bar"']);

        $this->assertNotNull($h5pContent1->filtered);
        $this->assertNotNull($h5pContent2->filtered);
        $this->assertNotNull($h5pContent3->filtered);

        $this->repository->clearFilteredParameters([$libraryId1]);

        $this->assertNull(H5PContent::find($h5pContent1->getKey())->filtered);
        $this->assertNull(H5PContent::find($h5pContent2->getKey())->filtered);
        $this->assertNotNull(H5PContent::find($h5pContent3->getKey())->filtered);
    }


    public function testGetNumAuthors(): void
    {
        H5PContent::factory()->create(['library_id' => 1, 'user_id' => 1]);
        H5PContent::factory()->create(['library_id' => 1, 'user_id' => 12]);

        $result = $this->repository->getNumAuthors();

        $this->assertEquals(2, $result);
    }

    public function testReplaceContentTypeCacheShouldCreateContentType(): void
    {
        $data = H5PContentTypeFixture::fixture()->count(3)->make()->get();

        $this->assertDatabaseCount('hh5p_libraries_hub_cache', 0);

        $this->repository->replaceContentTypeCache($data);

        $this->assertDatabaseCount('hh5p_libraries_hub_cache', 3);
    }

    public function testReplaceContentTypeCacheShouldTruncateExistingDataAndCreateContentType(): void
    {
        $data = H5PContentTypeFixture::fixture()->count(3)->make()->get();
        $this->repository->replaceContentTypeCache($data);

        $this->assertDatabaseCount('hh5p_libraries_hub_cache', 3);

        $data = H5PContentTypeFixture::fixture()->count(10)->make()->get();
        $this->repository->replaceContentTypeCache($data);

        $this->assertDatabaseCount('hh5p_libraries_hub_cache', 10);
    }
}
