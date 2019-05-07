<?php
namespace Metko\Metkontrol\Models;

use Illuminate\Support\Str;
use Metko\Metkontrol\Tests\Car;
use Illuminate\Database\Eloquent\Model;
use Metko\Metkontrol\Exceptions\RoleDoesNotExist;
use Metko\Metkontrol\Traits\MetkontrolPermission;

class Role extends Model{

    use MetkontrolPermission;

    protected $fillable = ['name', 'slug', 'level'];

    public function  __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('metkontrol.table_names.roles');
    }
    
    public function users()
    {
        return $this->morphedByMany(app("Metkontrol\User"), 
            config("metkontrol.fields.rollable"), null, null, config("metkontrol.fields.role_foreign_key"));
    }

    public function cars()
    {
        //return $this->belongsToMany(User::class);
        return $this->morphedByMany(Car::class, 
            config("metkontrol.fields.rollable") , null, null, config("metkontrol.fields.role_foreign_key"));
    }

   public static function boot()
    {
        parent::boot();

        static::creating(function($role)
        {
            $role->slug = Str::slug(Str::kebab($role->name));
        });

        static::updating(function($role)
        {
            $role->slug = Str::slug(Str::kebab($role->name));
        });
    }

    public static function findByName($name)
    {
        $role  = static::whereName($name)->first();
        if(empty($role)){
            $role  = static::whereSlug(Str::slug(Str::kebab($name)))->first();
        }
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