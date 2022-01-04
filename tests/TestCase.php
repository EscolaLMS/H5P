<?php

namespace EscolaLms\HeadlessH5P\Tests;

use EscolaLms\Core\EscolaLmsServiceProvider;
use EscolaLms\Core\Models\User;
use EscolaLms\HeadlessH5P\Database\Seeders\PermissionTableSeeder;
use EscolaLms\HeadlessH5P\Enums\H5PPermissionsEnum;
use Laravel\Passport\PassportServiceProvider;
use EscolaLms\HeadlessH5P\HeadlessH5PServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends \EscolaLms\Core\Tests\TestCase
{
    public $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionTableSeeder::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            HeadlessH5PServiceProvider::class,
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
            EscolaLmsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', true);
    }

    protected function authenticateAsAdmin(): void
    {
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_LIST);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_READ);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_DELETE);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_UPDATE);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_CREATE);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_LIBRARY_LIST);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_LIBRARY_READ);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_LIBRARY_DELETE);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_LIBRARY_UPDATE);
        $this->user->givePermissionTo(H5PPermissionsEnum::H5P_LIBRARY_CREATE);
    }
}
