<?php

namespace EscolaSoft\KibbleMad\Tests\Repositories;

use EscolaSoft\EscolaLms\Tests\ApiTestTrait;
use EscolaSoft\KibbleMad\Models\Mad;
use EscolaSoft\KibbleMad\Repositories\MadRepository;
use EscolaSoft\KibbleMad\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MadRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var MadRepository
     */
    protected $madRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->madRepo = \App::make(MadRepository::class);
    }

    /**
     * @test searchable_fields
     */
    public function test_searchable_fields()
    {
        $this->assertIsArray($this->madRepo->getFieldsSearchable());
    }

    /**
     * @test create
     */
    public function test_create_mad()
    {
        $mad = factory(Mad::class)->make()->toArray();

        $createdMad = $this->madRepo->create($mad);

        $createdMad = $createdMad->toArray();
        $this->assertArrayHasKey('id', $createdMad);
        $this->assertNotNull($createdMad['id'], 'Created Mad must have id specified');
        $this->assertNotNull(Mad::find($createdMad['id']), 'Mad with given id must be in DB');
        $this->assertModelData($mad, $createdMad);
    }

    /**
     * @test read
     */
    public function test_read_mad()
    {
        $mad = factory(Mad::class)->create();

        $dbMad = $this->madRepo->find($mad->id);

        $dbMad = $dbMad->toArray();
        $this->assertModelData($mad->toArray(), $dbMad);
    }

    /**
     * @test update
     */
    public function test_update_mad()
    {
        $mad = factory(Mad::class)->create();
        $fakeMad = factory(Mad::class)->make()->toArray();

        $updatedMad = $this->madRepo->update($fakeMad, $mad->id);

        $this->assertModelData($fakeMad, $updatedMad->toArray());
        $dbMad = $this->madRepo->find($mad->id);
        $this->assertModelData($fakeMad, $dbMad->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_mad()
    {
        $mad = factory(Mad::class)->create();

        $resp = $this->madRepo->delete($mad->id);

        $this->assertTrue($resp);
        $this->assertNull(Mad::find($mad->id), 'Mad should not exist in DB');
    }
}
