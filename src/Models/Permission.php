<?php
namespace Metko\Metkontrol\Models;

use Illuminate\Support\Str;
use Metko\Metkontrol\Tests\User;
use Illuminate\Database\Eloquent\Model;
use Metko\Metkontrol\Exceptions\PermissionDoesNotExist;
use Metko\Metkontrol\Exceptions\PermissionAlreadyExists;

class Permission extends Model{

   protected $fillable = ['name', 'slug'];

   public function roles()
   {
      return $this->morphedByMany(app('Metkontrol\Role'), 'permissionable');
   }
   public function users()
   {
      return $this->morphedByMany(app('Metkontrol\User'), 'permissionable');
   }


   public static function boot()
    {
        parent::boot();

        static::creating(function($permission)
        {
            $permission->slug = Str::slug($permission->name);
        });

        static::updating(function($permission)
        {
            $permission->slug = Str::slug($permission->name);
        });
    }

    public static function create(array $attributes = [])
    {
        $permission  = static::whereName($attributes['name'])->first();
        if(is_null($permission)){
            $permission  = static::whereSlug(Str::slug($attributes['name']))->first();
        }

        if (! is_null($permission)) {
            throw PermissionAlreadyExists::create($attributes['name']);
        }
        
        return static::query()->create($attributes);
    }


    public static function findByName($name)
    {
        $permission  = static::whereName($name)->first();
        if(is_null($permission)){
            $permission  = static::whereSlug(Str::slug($name))->first();
        }
        if(empty($permission)){
            throw PermissionDoesNotExist::named($name);
        }
        return $permission;
    }

    public static function findByID($id)
    {
        $permission  = static::find($id);
        if(empty($permission)){
            throw PermissionDoesNotExist::withId($id);
        }
        return $permission;
    }

    
}