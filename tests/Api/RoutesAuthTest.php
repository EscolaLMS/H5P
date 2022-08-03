<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Http\Middleware\QueryToken;
use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class RoutesAuthTest extends TestCase
{
    use H5PTestingTrait, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware([QueryToken::class, 'auth:api'])
            ->group(__DIR__ . './../../src/routes.php');
    }

    private function routeDataProvider(): array
    {
        $this->createApplication();
        $data = [];
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();
            preg_match('~{(.*?)}~', $uri, $output);

            if (str_contains($uri, '/hh5p/')) {
                $data[] = [
                    'route' => $uri,
                    'method' => strtolower($route->methods()[0]) . 'Json',
                    'state' => $output[1] ?? null,
                ];
            }
        }

        return $data;
    }

    /**
     * @dataProvider routeDataProvider
     */
    public function testAllRoutesShouldBeAuthorized(string $uri, string $method, ?string $state): void
    {
        switch ($state) {
            case "id":
                $uri = str_replace('{id}', '1', $uri);
                $response = $this->$method($uri);
                $response->assertUnauthorized();
                break;
            case "uuid":
                $uuid = $this->uploadHP5Content()->uuid;
                $uri = str_replace('{uuid}', $uuid, $uri);
                $response = $this->$method($uri);
                $response->assertOk();
                break;
            default:
                $response = $this->$method($uri, []);
                $response->assertUnauthorized();
        }
    }
}
