<?php

use Tests\TestCase;
// namespace Tests\Feature;

use App\Models\User;
use App\Models\Permission;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
// use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Foundation\Testing\RefreshDatabase;

    beforeEach(function () {
        Permission::insert([
            ['name'=>'INVITE_TEAM', 'description'=>'send invitation to team members to join your organization'],
            ['name'=>'INVITE_CLIENT', 'description'=>'send invitation to clients to join your organization'],
            ['name'=>'TEAM_VIEW', 'description'=>'view your team'],
            ['name'=>'TEAM_REMOVE_MEMBER', 'description'=>'remove team member'],
            ['name'=>'TEAM_ROLE_UPDATE', 'description'=>'update team member roles'],
            ['name'=>'ROLE_VIEW', 'description'=>'view roles'],
            ['name'=>'ROLE_ADD', 'description'=>'create new roles'],
            ['name'=>'ROLE_UPDATE', 'description'=>'update roles'],
            ['name'=>'ROLE_DELETE', 'description'=>'remove roles'],
            ['name'=>'JOB_VIEW', 'description'=>'view jobs'],
            ['name'=>'JOB_ADD', 'description'=>'add jobs'],
            ['name'=>'JOB_UPDATE', 'description'=>'update jobs'],
            ['name'=>'JOB_DELETE', 'description'=>'remove jobs'],
        ]);
    });


    it('should_assign_roles', function($role_1, $role_2){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $role_1 = $organization->roles()->create([
            'name' => $role_1['name'],
            'description' => $role_1['description']
        ]);
        $user->assignRole($role_1['name']);
        expect($user)->getRoleNames()->toContain($role_1['name']);
        $role_2 = $organization->roles()->create([
            'name' => $role_2['name'],
            'description' => $role_2['description'],
        ]);
        $user->assignRole($role_2['name']);

        expect($user)->fresh()->getRoleNames()->toContain($role_1['name'], $role_2['name']);
    })->with([
        [
            ['name'=> 'manager', 'description'=>'Manages the floor'],
            ['name'=> 'excecutive', 'description'=> 'Responsible for specific tasks' ]
        ],
        [
            ['name' => 'CEO', 'description'=> 'Manages the organization'],
            ['name' => 'Watchman', 'description' => 'Guards the organization']
        ]
    ]);

    it('should_give_users_permissions_direct', function(){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $permission = Permission::where('name', 'INVITE_TEAM')->first();
        $user->givePermissionTo($permission->name);
        expect($user)->userPermissions->toHaveCount(1)
        ->permissions()->toHaveCount(1)
        ->hasPermissionTo('INVITE_TEAM')->toBeTrue();
    });

    it('should_check_the_can_do_method', function(){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $permission = Permission::where('name', 'INVITE_CLIENT')->first();
        $user->givePermissionTo($permission->name);
        expect($user)->userPermissions->toHaveCount(1)
        ->permissions()->toHaveCount(1)
        ->can_do('INVITE_CLIENT')->toBeTrue();
    });

    it('should_give_users_permissions_via_role', function(){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $role = $organization->roles()->create([
            'name' => 'Manager',
            'description' => 'the user can edit the articles',
        ]);
        $user->assignRole('Manager');
        $permission = Permission::where('name', 'JOB_VIEW')->first();
        $role->givePermissionTo($permission->name);
        // dd($user->fresh()->rolePermissions);
        expect($user)
        ->fresh()->rolePermissions->toHaveCount(1)
        ->fresh()->permissions()->toHaveCount(1)
        ->hasPermissionTo('JOB_VIEW')->toBeTrue();
    });

    it('should_return_true_if_any_of_the_permission_is_present', function($permission, $permission_array){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $role = $organization->roles()->create([
            'name' => 'Manager',
            'description' => 'Manages the floor',
        ]);
        $user->assignRole('Manager');
        $permission = Permission::where('name', $permission)->first();
        $role->givePermissionTo($permission->name);
        expect($user->fresh())
            ->rolePermissions->toHaveCount(1)
            ->hasAnyPermission($permission_array)->toBeTrue();
    })->with([
            ['permission'=>'ROLE_VIEW', 'permission_array' => ['ROLE_VIEW', 'JOBS_VIEW']],
            ['permission'=>'ROLE_UPDATE', 'permission_array' => ['ROLE_VIEW', 'ROLE_UPDATE']]
    ]);

    it('should_return_false_if_any_of_the_permission_is_not_present', function($permission, $permission_array){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $role = $organization->roles()->create([
            'name' => 'Manager',
            'description' => 'Manages the floor',
        ]);
        $user->assignRole('Manager');
        dump($permission);
        $permission = Permission::where('name', $permission)->first();
        $role->givePermissionTo($permission->name);
        $this->assertTrue(empty(array_intersect($user->getPermissionNames()->toArray(), $permission_array)));
    })->with([
        ['permission'=>'ROLE_VIEW', 'permission_array' => ['JOBS_UPDATE', 'JOBS_VIEW']],
        ['permission'=>'ROLE_UPDATE', 'permission_array' => ['ROLE_VIEW', 'JOBS_VIEW']]
    ]);

    it('should_revoke_role_pemission_properly', function($permission, $permission_array){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $permission = Permission::where('name', $permission)->first();

        $user->givePermissionTo($permission->name);
        expect($user)
            ->userPermissions->toHaveCount(1)
            ->permissions()->toHaveCount(1)
            ->hasPermissionTo($permission->name)->toBeTrue();

        $user->fresh()->revokePermissionTo($permission->name);
        expect($user->fresh())
            ->userPermissions->toHaveCount(0)
            ->permissions()->toHaveCount(0)
            ->hasPermissionTo($permission->name)->toBeFalse();
    })->with([
            ['permission'=>'JOB_VIEW', 'permission_array' => ['TEAM_REMOVE_MEMBER', 'TEAM_ROLE_UPDATE']],
            ['permission'=>'INVITE_TEAM', 'permission_array' => ['JOB_VIEW', 'JOB_ADD']]
    ]);

    it('should_return_true_if_all_permissions_given_array_are_available', function($permissions, $permission_array){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        foreach ($permissions as $key => $permission) {
            $permission = Permission::where('name', $permission)->first();
            $user->fresh()->givePermissionTo($permission->name);
        }
        expect($user)
        ->userPermissions->toHaveCount(4)
        ->permissions()->toHaveCount(4)
        ->hasAllDirectPermissions($permission_array)->toBeTrue();
    })->with([
        ['permission'=> ['INVITE_CLIENT', 'INVITE_TEAM', 'TEAM_VIEW', 'TEAM_REMOVE_MEMBER',], 'permission_array' => ['INVITE_CLIENT', 'INVITE_TEAM', 'TEAM_VIEW']],
        ['permission'=> ['ROLE_VIEW', 'JOB_VIEW', 'TEAM_VIEW', 'ROLE_ADD',], 'permission_array' => ['ROLE_VIEW', 'JOB_VIEW', 'TEAM_VIEW']]
    ]);

    it('should_return_false_if_all_permissions_given_array_are_not_available', function($permissions, $permission_array){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        foreach ($permissions as $key => $permission) {
            $permission = Permission::where('name', $permission)->first();
            $user->fresh()->givePermissionTo($permission->name);
        }
        expect($user)->userPermissions->toHaveCount(3)
        ->permissions()->toHaveCount(3)
        ->hasAllDirectPermissions($permission_array)->toBeFalse();
    })->with([
            ['permission'=> ['INVITE_TEAM', 'TEAM_VIEW', 'TEAM_REMOVE_MEMBER',], 'permission_array' => ['INVITE_CLIENT', 'INVITE_TEAM', 'TEAM_VIEW']],
            ['permission'=> ['ROLE_VIEW', 'TEAM_VIEW', 'ROLE_ADD',], 'permission_array' => ['ROLE_VIEW', 'JOB_VIEW', 'TEAM_VIEW']]
    ]);


    it('should_return_true_if_any_permission_in_given_array_are_available', function($permissions, $permission_array){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        dump($permissions);
        foreach ($permissions as $key => $permission) {
            $permission = Permission::where('name', $permission)->first();
            $user->fresh()->givePermissionTo($permission->name);
        }
        expect($user)->userPermissions->toHaveCount(4)
        ->permissions()->toHaveCount(4)
        ->hasAnyDirectPermission($permission_array)->toBeTrue();
    })->with([
            ['permissions'=> ['JOB_DELETE', 'INVITE_TEAM', 'JOB_UPDATE', 'TEAM_REMOVE_MEMBER'], 'permission_array' => ['INVITE_CLIENT', 'INVITE_TEAM', 'TEAM_VIEW']],
            ['permissions'=> ['ROLE_VIEW', 'JOB_VIEW', 'TEAM_VIEW', 'ROLE_ADD'], 'permission_array' => ['ROLE_VIEW', 'JOB_VIEW', 'ROLE_DELETE']]
    ]);

    it('should_return_false_if_any_permissions_in_given_array_are_not_available', function($permissions, $permission_array){
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        foreach ($permissions as $key => $permission) {
            $permission = Permission::where('name', $permission)->first();
            $user->fresh()->givePermissionTo($permission->name);
        }
        expect($user)
        ->userPermissions->toHaveCount(3)
        ->permissions()->toHaveCount(3)
        ->hasAnyDirectPermission($permission_array)->toBeFalse();
    })->with([
            ['permission'=> ['INVITE_TEAM', 'TEAM_VIEW', 'TEAM_REMOVE_MEMBER',], 'permission_array' => ['INVITE_CLIENT', 'INVITE_VENDOR', 'TEAM_UPDATE']],
            ['permission'=> ['ROLE_VIEW', 'TEAM_VIEW', 'ROLE_ADD',], 'permission_array' => ['INVITE_CLIENT', 'JOB_VIEW', 'TEAM_EDIT']]
    ]);


    it('should_cache_permissions_once_permission_is_updated', function(){
        $user = User::factory()->create();
        $permission = 'ROLE_VIEW';
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $permission = Permission::where('name', $permission)->first();
        $user->givePermissionTo($permission->name);
        $cached_permissions = json_decode(Cache::get('user_'.$user->id.'_permissions'));
        expect($user->fresh())
        ->getPermissionNames()->contains($permission->name)->toBeTrue();
    });

    it('should_update_cache_permissions_once_permission_is_updated', function(){
        $user = User::factory()->create();
        $permission = 'ROLE_VIEW';
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id);
        $permission = Permission::where('name', $permission)->first();
        $user->givePermissionTo($permission->name);
        $cached_permissions = json_decode(Cache::get('user_'.$user->id.'_permissions'));
        $this->assertTrue($user->getPermissionNames()->contains($permission->name));

        $permission_2 = 'ROLE_UPDATE';
        $permission_2 = Permission::where('name', $permission_2)->first();
        $user->fresh()->givePermissionTo($permission_2->name);
        expect($user->fresh())
        ->getPermissionNames()->contains($permission->name)->toBeTrue()
        ->getPermissionNames()->contains($permission_2->name)->toBeTrue();
    });
