<?php

namespace Metko\Metkontrol;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Metko\Metkontrol\Exceptions\RoleDoesNotExist;
use Metko\Metkontrol\Exceptions\PermissionDoesNotExist;

class MetkontrolCache
{
    /**
     * app('Metkontrol\Role')->all().
     */
    protected $roles;

    /**
     * app('Metkontrol\Permission')->all().
     */
    protected $permissions;

    /**
     * app('Metkontrol\Permission::class').
     */
    protected $permissionClass;

    /**
     * app('Metkontrol\Role::class').
     */
    protected $roleClass;

    /**
     * Get roles stored in cache.
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get Permissions stored in cache.
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set the cache for the metkontrol package.
     */
    public function setCacheMetkontrol()
    {
        $this->permissionClass = app('Metkontrol\Permission');
        $this->roleClass = app('Metkontrol\Role');

        app()->singleton('metkontrol.roles', function ($app) {
            return cache()->rememberForever('metkontrol.roles', function () {
                return app('Metkontrol\Role')->all();
            });
        });

        app()->singleton('metkontrol.permissions', function ($app) {
            return cache()->rememberForever('metkontrol.permissions', function () {
                return app('Metkontrol\Permission')->all();
            });
        });

        $this->roles = app('metkontrol.roles');
        $this->permissions = app('metkontrol.permissions');
    }

    /**
     * Reset the cache.
     */
    public function resetCacheMetkontrol()
    {
        $this->roles = null;
        cache()->forget('metkontrol.roles');
        cache()->forget('metkontrol.permissions');
        $this->setCacheMetkontrol();
    }

    /**
     * Get a role stored in the cahche.
     *
     * @param mixed $role
     */
    public function getStoredRole($role, $column = null)
    {
        if (is_numeric($role)) {
            $roles = $this->getStoredRoleByID($role);
        } elseif (is_string($role)) {
            $roles = $this->getStoredRoleByName($role);
        }
        if (!is_null($column)) {
            $roles = $roles->pluck($column);
        }

        return $roles;
    }

    /**
     * Find a role stocked inthe cache by name.
     *
     * @param mixed $name
     */
    public function getStoredRoleByName($name)
    {
        $role = $this->roles->whereIn('name', $name)->first();
        if (empty($role)) {
            $role = $this->roles->whereIn('slug', Str::slug(Str::kebab($name)))->first();
        }
        if (empty($role)) {
            throw RoleDoesNotExist::named($name);
        }

        return $role;
    }

    /**
     * Get a role stocked in the cache by ID.
     *
     * @param mixed $id
     */
    public function getStoredRoleByID($id)
    {
        $role = $this->roles->find((int) $id);
        if (empty($role)) {
            throw RoleDoesNotExist::withId($id);
        }

        return $role;
    }

    /**
     * Get Permission stocked in the cache.
     *
     * @param mixed $permission
     * @param mixed $column
     */
    public function getStoredPermission($permission, $column = null)
    {
        $permission = checkPipeToArray($permission);

        if ($permission instanceof Collection) {
            $permission = $permission->toArray();
        }
        if (!is_array($permission)) {
            $permission = [$permission];
        }

        $permission = collect($permission)->map(function ($perm) {
            if (is_numeric($perm)) {
                return $this->getStoredPermissionByID($perm);
            }
            if (is_string($perm)) {
                return $this->getStoredPermissionByName($perm);
            }
            if (is_array($perm)) {
                return $this->getStoredPermissionByID($perm['id']);
            }
            if ($perm instanceof $this->permissionClass) {
                return $perm;
            }
            throw  PermissionDoesNotExist::withType(class_basename($perm));
        });

        if (!is_null($column)) {
            $permission = $permission->pluck($column);
        }
        if ($permission->count() == 1) {
            return $permission->first();
        }

        return $permission;
    }

    /**
     * Get permission stocked in cache by name.
     *
     * @param mixed $name
     */
    public function getStoredPermissionByName($name)
    {
        $permission = $this->roles->whereIn('name', $name)->first();

        if (empty($permission)) {
            $permission = $this->permissions->whereIn('slug', Str::slug(Str::kebab($name)))->first();
        }
        if (empty($permission)) {
            throw PermissionDoesNotExist::named($name);
        }

        return $permission;
    }

    /**
     * Get permission stocked in cache by ID..
     *
     * @param mixed $id
     */
    public function getStoredPermissionByID($id)
    {
        $permission = $this->permissions->find((int) $id);
        if (empty($permission)) {
            throw PermissionDoesNotExist::withId($id);
        }

        return $permission;
    }
}
