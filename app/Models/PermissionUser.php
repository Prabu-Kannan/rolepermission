<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PermissionUser extends Pivot
{
    use HasFactory;

    protected $table = 'permission_user';

    public $timestamps = null;
}
