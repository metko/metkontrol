<?php
namespace Metko\Metkontrol\Tests;

use Metko\Metkontrol\Models\Role;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Metko\Metkontrol\Traits\Metkontrol;
use Metko\Metkontrol\Traits\MetkontrolPermission;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthorizableContract, AuthenticatableContract
{
    use Metkontrol,
        MetkontrolPermission, 
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