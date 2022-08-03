<?php

namespace EscolaLms\HeadlessH5P\Tests\Unit;

use EscolaLms\HeadlessH5P\Http\Middleware\H5PLangMiddleware;
use EscolaLms\HeadlessH5P\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class H5PLangMiddlewareTest extends TestCase
{
    public function testAPIChangeH5PLangByGetParam(): void
    {
        Config::set('hh5p.language', 'en');
        $request = new Request(['lang' => 'pl']);

        $middleware = new H5PLangMiddleware();
        $middleware->handle($request, fn() => null);

        $this->assertEquals('pl', App::getLocale());
        $this->assertEquals('pl', Config::get('hh5p.language'));
    }

    public function testAPISetDefaultLang(): void
    {
        Config::set('hh5p.language', 'de');
        $request = new Request();

        $middleware = new H5PLangMiddleware();
        $middleware->handle($request, fn() => null);

        $this->assertEquals('de', App::getLocale());
        $this->assertEquals('de', Config::get('hh5p.language'));
    }
}
