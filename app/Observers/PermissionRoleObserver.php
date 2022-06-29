<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\PermissionRole;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PermissionRoleObserver
{
    /**
     * Handle the PermissionRole "created" event.
     *
     * @param  \App\Models\PermissionRole  $permissionRole
     * @return void
     */
    public function created(PermissionRole $permissionRole)
    {
        $this->updateRolePermissions($permissionRole);
    }

    /**
     * Handle the PermissionRole "updated" event.
     *
     * @param  \App\Models\PermissionRole  $permissionRole
     * @return void
     */
    public function updated(PermissionRole $permissionRole)
    {
       //
    }

    /**
     * Handle the PermissionRole "deleted" event.
     *
     * @param  \App\Models\PermissionRole  $permissionRole
     * @return void
     */
    public function deleted(PermissionRole $permissionRole)
    {
        $this->updateRolePermissions($permissionRole);
    }

    /**
     * Handle the PermissionRole "restored" event.
     *
     * @param  \App\Models\PermissionRole  $permissionRole
     * @return void
     */
    public function restored(PermissionRole $permissionRole)
    {
        $this->updateRolePermissions($permissionRole);
    }

    /**
     * Handle the PermissionRole "force deleted" event.
     *
     * @param  \App\Models\PermissionRole  $permissionRole
     * @return void
     */
    public function forceDeleted(PermissionRole $permissionRole)
    {
        $this->updateRolePermissions($permissionRole);
    }

    public function updateRolePermissions($permissionRole)
    {
        $role = Role::find($permissionRole->role_id);
        Cache::forever('role:'.$permissionRole->role_id.':permissions', $role->permissions);
    }
}
