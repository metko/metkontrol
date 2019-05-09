<?php

namespace Metko\Metkontrol\Traits;

use Metko\Metkontrol\Metkontrol;
use Illuminate\Support\Collection;
use Metko\Metkontrol\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Metko\Metkontrol\Exceptions\PermissionDoesNotExist;

trait MetkontrolPermission
{
    /**
     * getPermissionInstance.
     */
    public function getPermissionInstance()
    {
        return app('Metkontrol\Permission');
    }

    /**
     * permissions.
     */
    public function permissions()
    {
        return $this->morphToMany(
                  $this->getPermissionInstance(),
                  config('metkontrol.fields.permissionable'));
    }

    /**
     * givePermissionTo.
     *
     * @param mixed $permissions
     */
    public function givePermissionTo($permissions)
    {
        $this->permissions()->syncWithoutDetaching(
            $this->getStoredPermissions($permissions, 'id')
        );
        $this->load('permissions');

        return $this;
    }

    /**
     * An alias to hasPermissionTo(), but avoids throwing an exception.
     *
     * @param string|int|\Metko\Metkontrol\Contracts\Permission $permission
     * @param string|null                                       $guardName
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
     * Determine if the model may perform the given permission.
     *
     * @param string|int|\Metko\Metkontrol\Models\Permission $permission
     * @param string|null                                    $guardName
     *
     * @return bool
     *
     * @throws PermissionDoesNotExist
     */
    public function hasPermissionTo($permission): bool
    {
        $permission = $this->getStoredPermissions($permission);

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
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
        if (empty($permission->roles->first())) {
            return false;
        }

        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has all of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function hasAllPermissions($permissions): bool
    {
        $permissions = $this->convertInArrayWithKey($permissions, 'slug');
        foreach ($permissions as $permission) {
            if (!$this->checkPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function hasAnyPermission($permissions): bool
    {
        $permissions = $this->convertInArrayWithKey($permissions, 'slug');
        foreach ($permissions as $permission) {
            if ($this->checkPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|int|\Metko\Metkontrol\Permission $permission
     *
     * @return bool
     */
    public function hasDirectPermission($permission): bool
    {
        if (!$permission instanceof Permission) {
            $permission = $this->getStoredPermissions($permission);
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Revoke the given permission.
     *
     * @param \Metko\Metkontrol\Permission[]|string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permissions = null)
    {
        if (!is_null($permissions)) {
            $permissions = $this->getStoredPermissions($permissions, 'id');
        }
        $this->permissions()->detach($permissions);
        $this->load('permissions');

        return $this;
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
     * getPermissionNames.
     *
     * @param mixed $type
     *
     * @return Collection
     */
    public function getPermissionNames($type = 'name'): Collection
    {
        return $this->permissions->pluck($type);
    }

    /**
     * getArrayWithKey.
     *
     * @param mixed $permissions
     * @param mixed $key
     */
    protected function convertInArrayWithKey($permissions, $key)
    {
        $permissions = $this->getStoredPermissions($permissions, $key);
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        return $permissions;
    }

    /**
     * Scope the model query to certain permissions only.
     */
    public function scopePermission(Builder $query, $permissions)
    {
        $permissions = $this->convertToScopable($permissions);

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
     * @param string|array|\Metko\Metkontrol\Permissionn $permissions
     *
     * @return array
     */
    protected function convertToScopable($permissions): array
    {
        $permission = $this->getStoredPermissions($permissions);
        $scopeArray = [];
        if (!$permission instanceof Collection) {
            $scopeArray = [$permission];
        } else {
            foreach ($permission as $perm) {
                $scopeArray[] = $perm;
            }
        }

        return $scopeArray;
    }

    /**
     * getStoredPermissions.
     *
     * @param mixed $permissions
     * @param mixed $pluck
     */
    protected function getStoredPermissions($permissions, $pluck = null)
    {
        return app(Metkontrol::class)->getStoredPermission($permissions, $pluck);
    }
}
