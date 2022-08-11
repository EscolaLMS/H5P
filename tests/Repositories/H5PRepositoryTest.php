<?php

namespace EscolaLms\HeadlessH5P\Tests\Repositories;

use EscolaLms\HeadlessH5P\Repositories\H5PRepository;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use GuzzleHttp\Psr7\Response;
use H5PHubEndpoints;
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
}
