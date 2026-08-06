<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IntegrationCredential;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class IntegrationCredentialPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IntegrationCredential');
    }

    public function view(AuthUser $authUser, IntegrationCredential $integrationCredential): bool
    {
        return $authUser->can('View:IntegrationCredential');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IntegrationCredential');
    }

    public function update(AuthUser $authUser, IntegrationCredential $integrationCredential): bool
    {
        return $authUser->can('Update:IntegrationCredential');
    }

    public function delete(AuthUser $authUser, IntegrationCredential $integrationCredential): bool
    {
        return $authUser->can('Delete:IntegrationCredential');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IntegrationCredential');
    }

    public function restore(AuthUser $authUser, IntegrationCredential $integrationCredential): bool
    {
        return $authUser->can('Restore:IntegrationCredential');
    }

    public function forceDelete(AuthUser $authUser, IntegrationCredential $integrationCredential): bool
    {
        return $authUser->can('ForceDelete:IntegrationCredential');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IntegrationCredential');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IntegrationCredential');
    }

    public function replicate(AuthUser $authUser, IntegrationCredential $integrationCredential): bool
    {
        return $authUser->can('Replicate:IntegrationCredential');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IntegrationCredential');
    }
}
