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
                   config("metkontrol.fields.rollable"));
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
            if(is_numeric($role) || is_string($role) || $role instanceof Collection){
                  return $this->isContainInRoles($role);
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
            $roles = checkPipeToArray($roles);
            if(is_array($roles)){
                  foreach($roles as $r) if ($this->hasRole($r))  return true;          
            }
            return false;   
      }

      /**
       * isContainInRoles
       *
       * @param  mixed $roles
       *
       * @return void
       */
      public function isContainInRoles($roles)
      {
            if (is_numeric($roles)) {
                  return $this->roles->contains('id', $roles);
            }
            if (is_string($roles)) {
                  return $this->roles->contains('name', $roles) || 
                  $this->roles->contains('slug', $roles);
            }
            if($roles instanceof Collection){
                  $roles = $roles[0];
            }
            if ($roles instanceof Role) {
                  return $this->roles->contains('id', $roles->id);
            }
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
            $roles = checkPipeToArray($roles);
            if(is_numeric($roles) || is_string($roles) || $roles instanceof Role){
                  return $this->isContainInRoles($roles);
            }
            $roles = collect()->make($roles)->map(function ($role) {
                  if($role instanceof Role){
                        return $role->name;
                  }else{
                        if (is_numeric($role)) {
                              return $this->getRoleByID($role)->name;
                        }
                        if (is_string($role)) {
                              return $this->getRoleByName($role)->name;
                        }
                  }
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
            $role = checkPipeToArray($role);
            if(! is_array($role)) $role = [$role];

            return collect($role)->make($role)->map(function ($role, $key) {
                        if( ! $role instanceof Role){
                              if(is_numeric($role)){
                                    return $this->getRoleByID($role);
                              }
                              if(is_string($role)){
                                    return $this->getRoleByName($role);
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
