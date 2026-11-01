<?php

namespace Khurrambaig\HRManagement\Tests;

use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait;
    use HasRoles;

    protected $table = 'users';

    protected $guarded = [];

    public $timestamps = false;
}