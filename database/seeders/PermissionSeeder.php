<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
    }
}
