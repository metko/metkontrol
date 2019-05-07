<?php
namespace Metko\Metkontrol\Tests;

use Metko\Metkontrol\Models\Role;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Metko\Metkontrol\Traits\Metkontrol;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Car extends Model implements AuthorizableContract, AuthenticatableContract
{
    use Metkontrol, 
        Authorizable, 
        Authenticatable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
    public $timestamps = false;
    protected $table = 'users';
}