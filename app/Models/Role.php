<?php

namespace App\Models;

use App\Models\Permission;
use App\Models\Organization;
use App\Models\PermissionRole;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];


    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)->using(PermissionRole::class);
    }

    public function hasPermissionTo($permission)
    {
        $permissions = $this->permissions()->pluck('name');
        return $permissions->contains($permission);
    }

    public function givePermissionTo($action)
    {
        Log::info($action);
        $permission = Permission::where('name', $action)->first();
        $this->permissions()->attach($permission->id);
    }

    public function revokePermissionTo($action)
    {
        $permission = Permission::where('name', $action)->first();
        $this->permissions()->detach($permission->id);

        // PermissionRole::where([
        //     'role_id' => $this->id,
        //     'permission_id' => $permission->id
        // ])->delete();
    }

    public function hasAnyPermission($expected_permissions)
    {
        $available_permissions = $this->permissions()->pluck('name');
        return !empty(array_intersect($available_permissions, $expected_permissions));
    }

}
