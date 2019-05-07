<?php

namespace Metko\Metkontrol\Traits;

use Metko\Metkontrol\Models\Role;
use Illuminate\Support\Collection;
use Metko\Metkontrol\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Metko\Metkontrol\Exceptions\PermissionDoesNotExist;

trait MetkontrolPermission
{

      /**
       * getPermissionInstance
       *
       * @return void
       */
      public function getPermissionInstance(){
            return app('Metkontrol\Permission');
      }
      
      /**
       * permissions
       *
       * @return void
       */
      public function permissions()
      {
            return $this->morphToMany(
                  $this->getPermissionInstance(), 
                  config("metkontrol.fields.permissionable"));
      }

      /**
       * givePermissionTo
       *
       * @param  mixed $permissions
       *
       * @return void
       */
      public function givePermissionTo($permissions)
      {
            $this->permissions()->syncWithoutDetaching($this->mapPermissions($permissions));
            $this->load('permissions');
            return $this;
      }
      
      
      /**
       * Determine if the model may perform the given permission.
       *
       * @param string|int|\Metko\Metkontrol\Models\Permission $permission
       * @param string|null $guardName
       *
       * @return bool
       * @throws PermissionDoesNotExist
       */
      public function hasPermissionTo($permission): bool
      {
          
            if (is_string($permission)) {
                  $permission = $this->getPermissionInstance()->findByName($permission);
            }

            if (is_int($permission)) {                  
                  $permission = $this->getPermissionInstance()->findById($permission);
            }
            

            if (! $permission instanceof Permission) {
                  if(! is_null($permission)){
                        throw PermissionDoesNotExist::withType(get_class($permission));
                  }
                  throw new PermissionDoesNotExist;
            }

            return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
      }

      /**
       * An alias to hasPermissionTo(), but avoids throwing an exception.
       *
       * @param string|int|\Metko\Metkontrol\Contracts\Permission $permission
       * @param string|null $guardName
       *
       * @return bool
       */
      public function checkPermissionTo($permission): bool
      {
            try {
                  return $this->hasPermissionTo($permission);
            } catch (PermissionDoesNotExist $e) {
                  return false;
            }
      }

      /**
       * Determine if the model has, via roles, the given permission.
       *
       * @param \Metko\Metkontrol\Contracts\Permission $permission
       *
       * @return bool
       */
      protected function hasPermissionViaRole(Permission $permission): bool
      {
            if( empty($permission->roles->first())) return false;
            return $this->hasRole($permission->roles);
      }

      /**
       * Determine if the model has all of the given permissions.
       *
       * @param array ...$permissions
       *
       * @return bool
       * @throws \Exception
       */
      public function hasAllPermissions($permissions): bool
      {
            $permissions = $this->convertStringToArray($permissions);
            foreach ($permissions as $permission) {
                  if ( ! $this->checkPermissionTo($permission)) return false;
            }
            return true;
      }

      /**
       * Determine if the model has the given permission.
       *
       * @param string|int|\Metko\Metkontrol\Contracts\Permission $permission
       *
       * @return bool
       */
      public function hasDirectPermission($permission): bool
      {
            if (is_string($permission)) {
                  $permission = $this->getPermissionInstance()->findByName($permission);
                  if (! $permission) return false;
            }

            if (is_int($permission)) {
                  $permission = $this->getPermissionInstance()->findById($permission);
                  if (! $permission) return false;
            }

            if (! $permission instanceof Permission) return false;

            return $this->permissions->contains('id', $permission->id);
      }

   

      /**
       * Map the $role params 
       *
       * @param  mixed $role
       *
       * @return array with id
       */
      protected function mapPermissions($permissions): array
      {     
            $permissions = checkPipeToArray($permissions);

            if(! is_array($permissions)) $permissions = [$permissions];
            return collect($permissions)
                  ->map(function ($permissions, $key) {
                        if( ! $permissions instanceof Permission){
                              if(is_string($permissions)) return $this->getPermissionByName($permissions);
                              if(is_int($permissions))return $this->getPermissionByID($permissions);
                        }
                  return $permissions;
            })
            ->pluck('id')
            ->all();
      }

      /**
       * Revoke the given permission.
       *
       * @param \Metko\Metkontrol\Permission[]|string|string[] $permission
       *
       * @return $this
       */
      public function revokePermissionTo($permission)
      {
            $this->permissions()->detach($this->getStoredPermission($permission));
            $this->load('permissions');
            return $this;
      }

      /**
       * getStoredPermission
       *
       * @param  mixed $permissions
       *
       * @return void
       */
      protected function getStoredPermission($permissions)
      {

            if (is_numeric($permissions)) return $this->getPermissionInstance()->findById($permissions);

            if (is_string($permissions)) {
                  if (false !== strpos($permissions, '|')) {
                        $permissions = convertPipeToArray($permissions);
                  }else{
                        return $this->getPermissionInstance()->findByName($permissions);
                  }   
            }

            if (is_array($permissions)) {
                  foreach($permissions as $perm) $test[] = $this->getPermissionInstance()->findByName($perm);
                  return $this->getPermissionInstance()->whereIn('slug', $permissions)->get();
            }
            return $permissions;
      }

   
      /**
       * Determine if the model has any of the given permissions.
       *
       * @param array ...$permissions
       *
       * @return bool
       * @throws \Exception
       */
      public function hasAnyPermission($permissions): bool
      {
            $permissions = $this->convertStringToArray($permissions);
            foreach ($permissions as $permission) {
                  if ($this->checkPermissionTo($permission)) return true;
            }
            return false;
      }

      /**
       * convertStringToArray
       *
       * @param  mixed $permissions
       *
       * @return void
       */
      public function convertStringToArray($permissions)
      {
            if (is_string($permissions)) {
                  if (false !== strpos($permissions, '|')) {
                        $permissions = convertPipeToArray($permissions);
                  }else{
                        $permissions = [$permissions];
                  } 
            }
            return $permissions;
      }

      

      /**
       * @param string|array|\Metko\Metkontrol\Permissionn $permissions
       *
       * @return array
       */
      protected function convertToPermissionModels($permissions): array
      {
            $permissions = checkPipeToArray($permissions);
            if ($permissions instanceof Collection) {
                  $permissions = $permissions->all();
            }
            $permissions = is_array($permissions) ? $permissions : [$permissions];
            return array_map(function ($permission) {
                  if ($permission instanceof Permission) return $permission;
                  return $this->getPermissionInstance()->findByName($permission);

            }, $permissions);
      }

      /**
       * Scope the model query to certain permissions only.
       * 
      */
     public function scopePermission(Builder $query, $permissions)
     {
           $permissions = $this->convertToPermissionModels($permissions);

           $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
                 return array_merge($result, $permission->roles->all());
           }, []));

           return $query->where(function ($query) use ($permissions, $rolesWithPermissions) {
           
                 $query->whereHas('permissions', function ($query) use ($permissions) {
                       $query->where(function ($query) use ($permissions) {
                             foreach ($permissions as $permission) {
                             $query->orWhere(config('metkontrol.table_names.permissions').'.id', $permission->id);
                             }
                       });
                 });
                 
                 if (count($rolesWithPermissions) > 0) {
                       $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                             $query->where(function ($query) use ($rolesWithPermissions) {
                             foreach ($rolesWithPermissions as $role) {
                                   $query->orWhere(config('metkontrol.table_names.roles').'.id', $role->id);
                             }
                             });
                       });
                 }
           });
     }

     /**
       * getRoleByName
       *
       * @param string $name
       *
       * @return Role
       */
      public function getPermissionByName($name)
      {
            return $this->getPermissionInstance()->findByName($name);
      }

      /**
       * getRoleByID
       *
       * @param int $id
       *
       * @return Role
       */
      public function getPermissionByID($id): Role
      {
            return $this->getPermissionInstance()->findByID($id);
      }

      /**
       * Return all permissions directly coupled to the model.
       */
      public function getDirectPermissions(): Collection
      {
            return $this->permissions;
      }

      /**
       * Return all the permissions the model has via roles.
       */
      public function getPermissionsViaRoles(): Collection
      {
            return $this->load('roles', 'roles.permissions')
                  ->roles->flatMap(function ($role) {
                        return $role->permissions;
                  })->sort()->values();
      }

      /**
       * Return all the permissions the model has, both directly and via roles.
       *
       * @throws \Exception
       */
      public function getAllPermissions(): Collection
      {
            $permissions = $this->permissions;
            if ($this->roles) {
                  $permissions = $permissions->merge($this->getPermissionsViaRoles());
            }
            return $permissions->sort()->values();
      }

      /**
       * getPermissionNames
       *
       * @param  mixed $type
       *
       * @return Collection
       */
      public function getPermissionNames($type = "name"): Collection
      {
            return $this->permissions->pluck($type);
      }
}
