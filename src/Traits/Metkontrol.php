<?php

namespace Metko\Metkontrol\Traits;

use Metko\Metkontrol\Models\Role;
use Illuminate\Support\Collection;
use Metko\Metkontrol\Models\Permission;

trait Metkontrol
{     

       /**
       * getPermissionInstance
       *
       * @return void
       */
      public function getRoleInstance(){
            return app('Metkontrol\Role');
      }

      /**
       * roles
       *
       * @return void
       */
      public function roles()
      {
            return $this->morphToMany(
                   $this->getRoleInstance(),
                   'rollable');
      }

     /**
     * Assign a given role or multiple role to the model.
     *
     * @param array|string|\Metko\Metkontrol\Role ...$roles
     *
     * 
     */
      public function assignRole($role) 
      {
            $this->roles()->syncWithoutDetaching($this->mapRoles($role));
            $this->load('roles');
            return $this;
      }

      /**
       * Remove 
       *
       * @param  mixed $role
       *
       * @return void
       */
      public function removeRole($role = [])
      {
            if(empty($role)){
                  $this->roles()->detach();
            } else{
                  $this->roles()->detach($this->mapRoles($role));
            }
            $this->load('roles');
            return $this;
      }

      /**
       * Check if the current model has the given role
       *
       * @param  mixed $role
       *
       * @return bool
       */
      public function hasRole($role): bool
      {
            //dd($role);
          
            if(is_string($role)){
                  return $this->roles->contains('name', $role) ||
                        $this->roles->contains('slug', $role);
            }
            if(is_int($role)){
                  
                  return $this->roles->contains('id', $role);
            }

            if($role instanceof Collection){
                  $role = $role[0];
            }

            return $this->roles->contains($role);
      }

      /**
       * hasAnyRole
       *
       * @param  mixed $roles
       *
       * @return void
       */
      public function hasAnyRole($roles)
      {

            if (is_string($roles) && false !== strpos($roles, '|')) {
                  $roles = convertPipeToArray($roles);
            }

            if(is_array($roles)){
                  foreach($roles as $r) if ($this->hasRole($r))  return true;          
            }
            return false;   
      }

      /**
       * Determine if the model has all of the given role(s).
       *
       * @param string|\Metko\Metkontrol\Models $roles
       *
       * @return bool
       */
      public function hasAllRoles($roles): bool
      {
            if (is_string($roles) && false !== strpos($roles, '|')) {
                  $roles = convertPipeToArray($roles);
            }
            if (is_string($roles)) {
                  return $this->roles->contains('name', $roles);
            }
            if ($roles instanceof Role) {
                  return $this->roles->contains('id', $roles->id);
            }
            $roles = collect()->make($roles)->map(function ($role) {
                  return $role instanceof Role ? $role->name : $role;
            });

            return $roles->intersect($this->getRoleNames()) == $roles;
      } 

    

      /**
       * getRoleNames
       *
       * @return Collection
       */
      public function getRoleNames(): Collection
      {
            return $this->roles->pluck('name');
      }
      /**
       * Map the $role params 
       *
       * @param  mixed $role
       *
       * @return array with id
       */
      protected function mapRoles($role): array
      {
            if (is_string($role) && false !== strpos($role, '|')) {
                  $role = convertPipeToArray($role);
            }
            if(! is_array($role)) $role = [$role];
            return collect($role)
                  ->map(function ($role, $key) {
                        if( ! $role instanceof Role){
                              if(is_string($role)){
                                    return $this->getRoleByName($role);
                              }
                              if(is_int($role)){
                                    return $this->getRoleByID($role);
                              }
                        }
                        return $role;
                  })
                  ->pluck('id')
                  ->all();
      }

      /**
       * getRoleByName
       *
       * @param string $name
       *
       * @return Role
       */
      public function getRoleByName($name): Role
      {
            return Role::findByName($name);
      }

      /**
       * getRoleByID
       *
       * @param int $id
       *
       * @return Role
       */
      public function getRoleByID($id): Role
      {
            return Role::findByID($id);
      }
}
