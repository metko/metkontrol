<?php
namespace Metko\Metkontrol\Models;

use Illuminate\Support\Str;
use Metko\Metkontrol\Tests\Car;
use Metko\Metkontrol\Tests\User;
use Illuminate\Database\Eloquent\Model;
use Metko\Metkontrol\Exceptions\RoleDoesNotExist;
use Metko\Metkontrol\Traits\MetkontrolPermission;

class Role extends Model{

   protected $fillable = ['name', 'slug', 'level'];

   use MetkontrolPermission;

    public function users()
    {
        return $this->morphedByMany(config('metkontrol.models.user'), 'rollable');
    }

    public function cars()
    {
        //return $this->belongsToMany(User::class);
        return $this->morphedByMany(Car::class, 'rollable');
    }

   public static function boot()
    {
        parent::boot();

        static::creating(function($role)
        {
            $role->slug = Str::slug($role->name);
        });

        static::updating(function($role)
        {
            $role->slug = Str::slug($role->name);
        });
    }

    public static function findByName($name)
    {
        $role  = static::whereName($name)->first();
        if(empty($role)){
            throw RoleDoesNotExist::named($name);
        }
        return $role;
    }

    public static function findByID($id)
    {
        $role  = static::find($id);
        if(empty($role)){
            throw RoleDoesNotExist::withId($id);
        }
        return $role;
    }

}