<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductCardSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ProductCardSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProductCardSetting');
    }

    public function view(AuthUser $authUser, ProductCardSetting $productCardSetting): bool
    {
        return $authUser->can('View:ProductCardSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProductCardSetting');
    }

    public function update(AuthUser $authUser, ProductCardSetting $productCardSetting): bool
    {
        return $authUser->can('Update:ProductCardSetting');
    }

    public function delete(AuthUser $authUser, ProductCardSetting $productCardSetting): bool
    {
        return $authUser->can('Delete:ProductCardSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ProductCardSetting');
    }

    public function restore(AuthUser $authUser, ProductCardSetting $productCardSetting): bool
    {
        return $authUser->can('Restore:ProductCardSetting');
    }

    public function forceDelete(AuthUser $authUser, ProductCardSetting $productCardSetting): bool
    {
        return $authUser->can('ForceDelete:ProductCardSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProductCardSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProductCardSetting');
    }

    public function replicate(AuthUser $authUser, ProductCardSetting $productCardSetting): bool
    {
        return $authUser->can('Replicate:ProductCardSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProductCardSetting');
    }
}
