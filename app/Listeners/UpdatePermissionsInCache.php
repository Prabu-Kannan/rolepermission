<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use App\Events\PermissionsChangedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePermissionsInCache
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PermissionsChanged  $event
     * @return void
     */
    public function handle(PermissionsChangedEvent $event)
    {
        Cache::put('user:'.$event->user->id.':permissions', $event->user->permissions, now()->addHours(2));
    }
}
