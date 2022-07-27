<?php

namespace EscolaLms\HeadlessH5P\Database\Seeders;

use EscolaLms\HeadlessH5P\Enums\H5PPermissionsEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionTableSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $apiAdmin = Role::findOrCreate('admin', 'api');
        foreach (H5PPermissionsEnum::getValues() as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        $apiAdmin->givePermissionTo(H5PPermissionsEnum::getValues());
    }
}
