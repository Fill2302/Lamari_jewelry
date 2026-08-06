<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PaymentRoutingSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PaymentRoutingSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaymentRoutingSetting');
    }

    public function view(AuthUser $authUser, PaymentRoutingSetting $paymentRoutingSetting): bool
    {
        return $authUser->can('View:PaymentRoutingSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaymentRoutingSetting');
    }

    public function update(AuthUser $authUser, PaymentRoutingSetting $paymentRoutingSetting): bool
    {
        return $authUser->can('Update:PaymentRoutingSetting');
    }

    public function delete(AuthUser $authUser, PaymentRoutingSetting $paymentRoutingSetting): bool
    {
        return $authUser->can('Delete:PaymentRoutingSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PaymentRoutingSetting');
    }

    public function restore(AuthUser $authUser, PaymentRoutingSetting $paymentRoutingSetting): bool
    {
        return $authUser->can('Restore:PaymentRoutingSetting');
    }

    public function forceDelete(AuthUser $authUser, PaymentRoutingSetting $paymentRoutingSetting): bool
    {
        return $authUser->can('ForceDelete:PaymentRoutingSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaymentRoutingSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaymentRoutingSetting');
    }

    public function replicate(AuthUser $authUser, PaymentRoutingSetting $paymentRoutingSetting): bool
    {
        return $authUser->can('Replicate:PaymentRoutingSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaymentRoutingSetting');
    }
}
