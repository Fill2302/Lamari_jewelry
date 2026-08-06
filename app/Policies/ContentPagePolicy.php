<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ContentPage;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ContentPagePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContentPage');
    }

    public function view(AuthUser $authUser, ContentPage $contentPage): bool
    {
        return $authUser->can('View:ContentPage');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContentPage');
    }

    public function update(AuthUser $authUser, ContentPage $contentPage): bool
    {
        return $authUser->can('Update:ContentPage');
    }

    public function delete(AuthUser $authUser, ContentPage $contentPage): bool
    {
        return $authUser->can('Delete:ContentPage');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ContentPage');
    }

    public function restore(AuthUser $authUser, ContentPage $contentPage): bool
    {
        return $authUser->can('Restore:ContentPage');
    }

    public function forceDelete(AuthUser $authUser, ContentPage $contentPage): bool
    {
        return $authUser->can('ForceDelete:ContentPage');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContentPage');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContentPage');
    }

    public function replicate(AuthUser $authUser, ContentPage $contentPage): bool
    {
        return $authUser->can('Replicate:ContentPage');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContentPage');
    }
}
