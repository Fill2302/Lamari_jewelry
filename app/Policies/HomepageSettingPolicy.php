<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HomepageSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HomepageSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HomepageSetting');
    }

    public function view(AuthUser $authUser, HomepageSetting $homepageSetting): bool
    {
        return $authUser->can('View:HomepageSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HomepageSetting');
    }

    public function update(AuthUser $authUser, HomepageSetting $homepageSetting): bool
    {
        return $authUser->can('Update:HomepageSetting');
    }

    public function delete(AuthUser $authUser, HomepageSetting $homepageSetting): bool
    {
        return $authUser->can('Delete:HomepageSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HomepageSetting');
    }

    public function restore(AuthUser $authUser, HomepageSetting $homepageSetting): bool
    {
        return $authUser->can('Restore:HomepageSetting');
    }

    public function forceDelete(AuthUser $authUser, HomepageSetting $homepageSetting): bool
    {
        return $authUser->can('ForceDelete:HomepageSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HomepageSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HomepageSetting');
    }

    public function replicate(AuthUser $authUser, HomepageSetting $homepageSetting): bool
    {
        return $authUser->can('Replicate:HomepageSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HomepageSetting');
    }
}
