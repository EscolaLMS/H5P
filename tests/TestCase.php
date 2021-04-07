<?php

namespace EscolaLms\HeadlessH5P\Tests;

use App\Models\User;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use EscolaLms\HeadlessH5P\HeadlessH5PServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [HeadlessH5PServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', true);
    }
}
