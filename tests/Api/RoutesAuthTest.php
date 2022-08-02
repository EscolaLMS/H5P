<?php

namespace EscolaLms\HeadlessH5P\Tests\Api;

use EscolaLms\HeadlessH5P\Http\Middleware\QueryToken;
use EscolaLms\HeadlessH5P\Tests\Traits\H5PTestingTrait;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Support\Facades\Route;



class RoutesAuthTest extends TestCase
{

    public function testAllRoutesShouldBeAuthorized(): void
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            if (strpos($route->uri(), '/hh5p/') !== false) {
                $method = strtolower($route->methods()[0]);
                $uri = $route->uri();
                switch ($uri) {
                    case  "api/hh5p/content/{uuid}":
                        $response = $this->$method($uri);
                        $response->assertOk();
                    default:
                        $response = $this->$method($uri);
                        $response->assertForbidden();
                }
            }
        }
    }
}
