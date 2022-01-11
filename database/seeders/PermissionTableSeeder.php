<?php

namespace EscolaLms\HeadlessH5P\Database\Seeders;

use EscolaLms\HeadlessH5P\Enums\H5PPermissionsEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @todo remove neccesity of using 'web' guard
 */
class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $apiAdmin = Role::findOrCreate('admin', 'api');
        $webAdmin = Role::findOrCreate('admin', 'web');
        $permissions = [
            H5PPermissionsEnum::H5P_LIST,
            H5PPermissionsEnum::H5P_READ,
            H5PPermissionsEnum::H5P_DELETE,
            H5PPermissionsEnum::H5P_UPDATE,
            H5PPermissionsEnum::H5P_CREATE,
            H5PPermissionsEnum::H5P_LIBRARY_LIST,
            H5PPermissionsEnum::H5P_LIBRARY_READ,
            H5PPermissionsEnum::H5P_LIBRARY_DELETE,
            H5PPermissionsEnum::H5P_LIBRARY_UPDATE,
            H5PPermissionsEnum::H5P_LIBRARY_CREATE,
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
            Permission::findOrCreate($permission, 'web');
        }

        $apiAdmin->givePermissionTo($permissions);
        $webAdmin->givePermissionTo($permissions);
    }
}
