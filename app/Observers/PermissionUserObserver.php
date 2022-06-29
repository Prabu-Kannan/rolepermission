<?php

namespace App\Observers;

use App\Models\User;
use App\Models\PermissionUser;
use Illuminate\Support\Facades\Cache;

class PermissionUserObserver
{
    /**
     * Handle the PermissionUser "created" event.
     *
     * @param  \App\Models\PermissionUser  $permissionUser
     * @return void
     */
    public function created(PermissionUser $permissionUser)
    {
        $this->updateRolePermissions($permissionUser);
    }

    /**
     * Handle the PermissionUser "updated" event.
     *
     * @param  \App\Models\PermissionUser  $permissionUser
     * @return void
     */
    public function updated(PermissionUser $permissionUser)
    {
        //
    }

    /**
     * Handle the PermissionUser "deleted" event.
     *
     * @param  \App\Models\PermissionUser  $permissionUser
     * @return void
     */
    public function deleted(PermissionUser $permissionUser)
    {
        $this->updateRolePermissions($permissionUser);
    }

    /**
     * Handle the PermissionUser "restored" event.
     *
     * @param  \App\Models\PermissionUser  $permissionUser
     * @return void
     */
    public function restored(PermissionUser $permissionUser)
    {
        $this->updateRolePermissions($permissionUser);
    }

    /**
     * Handle the PermissionUser "force deleted" event.
     *
     * @param  \App\Models\PermissionUser  $permissionUser
     * @return void
     */
    public function forceDeleted(PermissionUser $permissionUser)
    {
        $this->updateRolePermissions($permissionUser);
    }

    public function updateRolePermissions($permissionUser)
    {
        $user = User::find($permissionUser->user_id);
        Cache::put('user:'.$user->id.':permissions', $user->permissions, now()->addHours(2));
    }
}
