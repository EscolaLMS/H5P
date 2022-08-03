<?php
namespace EscolaLms\HeadlessH5P\Tests\Repositories;


use EscolaLms\HeadlessH5P\Models\H5PLibrary;
use EscolaLms\HeadlessH5P\Repositories\H5PEditorAjaxRepository;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use H5PEditorAjaxInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class H5PEditorAjaxRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private H5PEditorAjaxRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app()->make(H5PEditorAjaxRepository::class);
    }

    public function testGetLatestLibraryVersion(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 2, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 0, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test2', 'major_version' => 1, 'minor_version' => 0, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test3', 'major_version' => 1, 'minor_version' => 0, 'patch_version' => 1]);

        $result = $this->repository->getLatestLibraryVersions();

        $this->assertCount(3, $result);
        $this->assertCount(1, $result->where('name', 'Test1'));
        $this->assertCount(1, $result->where('name', 'Test2'));
        $this->assertCount(1, $result->where('name', 'Test3'));
        $this->assertEquals('1.2.1', $result->firstWhere('name', 'Test1')->version);
        $this->assertEquals('1.0.1', $result->firstWhere('name', 'Test2')->version);
        $this->assertEquals('1.0.1', $result->firstWhere('name', 'Test3')->version);
    }

    public function testGetLatestLibraryMajorVersion(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 2, 'minor_version' => 1, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 1, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test2', 'major_version' => 2, 'minor_version' => 1, 'patch_version' => 1]);

        $result = $this->repository->getLatestLibraryVersions();

        $this->assertCount(2, $result);
        $this->assertCount(1, $result->where('name', 'Test1'));
        $this->assertCount(1, $result->where('name', 'Test2'));
        $this->assertEquals('2.1.1', $result->firstWhere('name', 'Test1')->version);
        $this->assertEquals('2.1.1', $result->firstWhere('name', 'Test2')->version);
    }

    public function testGetLatestLibraryMinorVersion(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 1, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 3, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test2', 'major_version' => 1, 'minor_version' => 1, 'patch_version' => 1]);

        $result = $this->repository->getLatestLibraryVersions();

        $this->assertCount(2, $result);
        $this->assertCount(1, $result->where('name', 'Test1'));
        $this->assertCount(1, $result->where('name', 'Test2'));
        $this->assertEquals('1.3.1', $result->firstWhere('name', 'Test1')->version);
        $this->assertEquals('1.1.1', $result->firstWhere('name', 'Test2')->version);
    }

    public function testGetLatestLibraryPatchVersion(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 2, 'minor_version' => 1, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 2, 'minor_version' => 1, 'patch_version' => 2]);
        H5PLibrary::factory()->create(['name' => 'Test2', 'major_version' => 2, 'minor_version' => 1, 'patch_version' => 2]);

        $result = $this->repository->getLatestLibraryVersions();

        $this->assertCount(2, $result);
        $this->assertCount(1, $result->where('name', 'Test1'));
        $this->assertCount(1, $result->where('name', 'Test2'));
        $this->assertEquals('2.1.2', $result->firstWhere('name', 'Test1')->version);
        $this->assertEquals('2.1.2', $result->firstWhere('name', 'Test2')->version);
    }

    public function testGetLatestLibraryVersionSameName(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 2, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 0, 'patch_version' => 1]);

        $result = $this->repository->getLatestLibraryVersions();

        $this->assertCount(1, $result);
        $this->assertCount(1, $result->where('name', 'Test1'));
        $this->assertEquals('1.2.1', $result->firstWhere('name', 'Test1')->version);
    }

    public function testGetLatestLibraryVersionSameVersions(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 1, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 1, 'patch_version' => 1]);

        $result = $this->repository->getLatestLibraryVersions();

        $this->assertCount(1, $result);
        $this->assertCount(1, $result->where('name', 'Test1'));
        $this->assertEquals('1.1.1', $result->firstWhere('name', 'Test1')->version);
    }

    public function testGetLatestLibraryVersionOnlyUniques(): void
    {
        H5PLibrary::factory()->create(['name' => 'Test1', 'major_version' => 1, 'minor_version' => 1, 'patch_version' => 1]);
        H5PLibrary::factory()->create(['name' => 'Test2', 'major_version' => 2, 'minor_version' => 2, 'patch_version' => 2]);
        H5PLibrary::factory()->create(['name' => 'Test3', 'major_version' => 3, 'minor_version' => 3, 'patch_version' => 3]);
        H5PLibrary::factory()->create(['name' => 'Test4', 'major_version' => 4, 'minor_version' => 4, 'patch_version' => 4]);

        $result = $this->repository->getLatestLibraryVersions();

        $this->assertCount(4, $result);
        $this->assertCount(1, $result->where('name', 'Test1'));
        $this->assertCount(1, $result->where('name', 'Test2'));
        $this->assertCount(1, $result->where('name', 'Test3'));
        $this->assertCount(1, $result->where('name', 'Test4'));
        $this->assertEquals('1.1.1', $result->firstWhere('name', 'Test1')->version);
        $this->assertEquals('2.2.2', $result->firstWhere('name', 'Test2')->version);
        $this->assertEquals('3.3.3', $result->firstWhere('name', 'Test3')->version);
        $this->assertEquals('4.4.4', $result->firstWhere('name', 'Test4')->version);
    }
}
