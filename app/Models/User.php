<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Permission;
use App\Models\Organization;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Events\PermissionsChangedEvent;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships as HasDeepRelationships;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasDeepRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function userPermissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function rolePermissions()
    {
        return $this->hasManyDeep(Permission::class, ['role_user', Role::class, 'permission_role']);
    }

    public function allPermissions()
    {
        $permissions = $this->userPermissions->merge($this->rolePermissions);
        return $permissions;
    }

    public function permissions()
    {
        $permissions = $this->getCachedPermissions();

        return $permissions;
    }

    public function getPermissionNames()
    {
        $permission_names = $this->permissions()->pluck('name');
        return $permission_names;
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    public function getRoleNames()
    {
        return $this->roles->pluck('name');
    }

    public function assignRole($role_name)
    {
        $role = Role::where('name', $role_name)->first();
        $this->roles()->attach($role->id);
        $this->updateCachedPermissions();
    }

    public function hasPermissionTo($permission)
    {
        // dd($this->getPermissionNames());
        $permissions = $this->getPermissionNames();
        return $permissions->contains($permission);
    }

    public function givePermissionTo($action, $organization=NULL)
    {
        $permission = Permission::where('name', $action)->first();
        $organization = $organization ?? $this->organizations->first();
        $this->userPermissions()->attach($permission->id, ['organization_id' => $organization->id]);
        $this->updateCachedPermissions();
    }

    public function revokePermissionTo($action, $organization=NULL)
    {
        $permission = Permission::where('name', $action)->first();
        $organization = $organization ?? $this->organizations->first();
        DB::table('permission_user')->where([
            'user_id'=> $this->id,
            'permission_id' => $permission->id,
            'organization_id' => $organization->id
        ])->delete();
        $this->updateCachedPermissions();
    }

    public function hasAnyPermission($expected_permissions)
    {
        $available_permissions = $this->permissions()->pluck('name')->toArray();
        return !empty(array_intersect($available_permissions, $expected_permissions));
    }

    public function hasDirectPermission($permission)
    {
        $permission = Permission::where('name', $permission);
        $available_permissions = $this->userPermissions->pluck('name');
        return in_array($permission, $available_permissions);
    }

    public function hasAllDirectPermissions($permissions_array)
    {
        $available_permissions = $this->userPermissions->pluck('name');
        $expected_count = count($permissions_array);
        $similar_permissions = array_intersect($permissions_array, $available_permissions->toArray());
        $similar_count = count($similar_permissions);
        return $expected_count == $similar_count;
    }

    public function hasAnyDirectPermission($permissions_array)
    {
        $available_permissions = $this->userPermissions->pluck('name');
        $expected_count = count($permissions_array);
        $similar_permissions = array_intersect($permissions_array, $available_permissions->toArray());
        $similar_count = count($similar_permissions);
        return $similar_count > 0;
    }

    public function can_do($action)
    {
        return $this->hasPermissionTo($action);
    }

    public function updateCachedPermissions()
    {
        PermissionsChangedEvent::dispatch($this);
    }

    public function getCachedPermissions()
    {
        $permissions = json_decode(Cache::get('user_'.$this->id.'_permissions'));
        if(!$permissions){
            $this->updateCachedPermissions();
            $permissions = json_decode(Cache::get('user_'.$this->id.'_permissions'));
        }
        return Permission::hydrate($permissions);
    }

    public function getCachedPermissionNames()
    {
        $permission_names = $this->getCachedPermissions()->pluck('name');
        return $permission_names;
    }

}
