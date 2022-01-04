<?php

namespace EscolaLms\HeadlessH5P\Policies;

use EscolaLms\Core\Models\User;
use EscolaLms\HeadlessH5P\Enums\H5PPermissionsEnum;
use Illuminate\Auth\Access\HandlesAuthorization;

class H5PLibraryPolicy
{
    use HandlesAuthorization;

    public function list(?User $user): bool
    {
        return $user && $user->can(H5PPermissionsEnum::H5P_LIBRARY_LIST);
    }

    public function read(?User $user): bool
    {
        return $user && $user->can(H5PPermissionsEnum::H5P_LIBRARY_READ);
    }

    public function create(?User $user): bool
    {
        return $user && $user->can(H5PPermissionsEnum::H5P_LIBRARY_CREATE);
    }

    public function delete(?User $user): bool
    {
        return $user && $user->can(H5PPermissionsEnum::H5P_LIBRARY_DELETE);
    }

    public function update(?User $user): bool
    {
        return $user && $user->can(H5PPermissionsEnum::H5P_LIBRARY_UPDATE);
    }
}
