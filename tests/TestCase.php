<?php

namespace EscolaLms\HeadlessH5P\Tests;

use EscolaLms\Core\EscolaLmsServiceProvider;
use EscolaLms\Core\Models\User;
use EscolaLms\HeadlessH5P\Database\Seeders\PermissionTableSeeder;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Laravel\Passport\PassportServiceProvider;
use EscolaLms\HeadlessH5P\HeadlessH5PServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends \EscolaLms\Core\Tests\TestCase
{
    public $user;

    protected MockHandler $mock;

    protected $container = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionTableSeeder::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            HeadlessH5PServiceProvider::class,
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
            EscolaLmsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->mock = new MockHandler([new Response(200, [], 'Hello, World')]);
        $handlerStack = HandlerStack::create($this->mock);

        // Setup default database to use sqlite :memory:
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', true);
        $app['config']->set('hh5p.guzzle', ['handler' => $handlerStack]);
    }

    protected function authenticateAsAdmin(): void
    {
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');
    }

    protected function authenticateAsUser(): void
    {
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
    }
}
