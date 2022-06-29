<?php

namespace App\Providers;

use App\Models\PermissionRole;
use App\Models\PermissionUser;
use Illuminate\Support\ServiceProvider;
use App\Observers\PermissionRoleObserver;
use App\Observers\PermissionUserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        PermissionUser::observe(PermissionUserObserver::class);
        PermissionRole::observe(PermissionRoleObserver::class);
    }
}
