<?php

namespace Metko\Metkontrol\Traits;

use Metko\Metkontrol\Metkontrol;
use Metko\Metkontrol\Models\Role;
use Illuminate\Support\Collection;

trait MetkontrolRole
{
    /**
     * getPermissionInstance.
     */
    public function getRoleInstance()
    {
        return app('Metkontrol\Role');
    }

    /**
     * roles.
     */
    public function roles()
    {
        return $this->morphToMany(
                   $this->getRoleInstance(),
                   config('metkontrol.fields.rollable'));
    }

    /**
     * Assign a given role or multiple role to the model.
     *
     * @param array|string|\Metko\Metkontrol\Role ...$roles
     */
    public function assignRole($role)
    {
        $this->roles()->syncWithoutDetaching($this->mapRoles($role));
        $this->load('roles');

        return $this;
    }

    /**
     * Remove.
     *
     * @param mixed $role
     */
    public function removeRole($role = [])
    {
        if (empty($role)) {
            $this->roles()->detach();
        } else {
            $this->roles()->detach($this->mapRoles($role));
        }
        $this->load('roles');

        return $this;
    }

    /**
     * Check if the current model has the given role.
     *
     * @param mixed $role
     *
     * @return bool
     */
    public function hasRole($role): bool
    {
        if (is_numeric($role) || is_string($role) || $role instanceof Collection) {
            return $this->isContainInRoles($role);
        }

        return $this->roles->contains($role);
    }

    /**
     * hasAnyRole.
     *
     * @param mixed $roles
     */
    public function hasAnyRole($roles)
    {
        $roles = checkPipeToArray($roles);
        if (is_array($roles)) {
            foreach ($roles as $r) {
                if ($this->hasRole($r)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * isContainInRoles.
     *
     * @param mixed $roles
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
        if ($roles instanceof Collection) {
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
        if (is_numeric($roles) || is_string($roles) || $roles instanceof Role) {
            return $this->isContainInRoles($roles);
        }
        $roles = collect($roles)->map(function ($role) {
            if ($role instanceof Role) {
                return $role->name;
            } else {
                return $this->getStoredRole($role)->name;
            }

            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->getRoleNames()) == $roles;
    }

    /**
     * getRoleNames.
     *
     * @return Collection
     */
    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    /**
     * Map the $role params.
     *
     * @param mixed $role
     *
     * @return array with id
     */
    protected function mapRoles($role): array
    {
        $role = checkPipeToArray($role);
        if (!is_array($role)) {
            $role = [$role];
        }

        return collect($role)
            ->map(function ($role) {
                if (!$role instanceof Role) {
                    return $this->getStoredRole($role);
                }

                return $role;
            })
            ->pluck('id')
            ->all();
    }

    /**
     * get role stored in cache.
     *
     * @param string $name
     *
     * @return Role
     */
    public function getStoredRole($role, $column = null): Role
    {
        return app(Metkontrol::class)->getStoredRole($role, $column);
    }
}
