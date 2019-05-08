<?php

namespace Metko\Metkontrol;

class MetkontrolCache
{
    /**
     * app('Metkontrol\Role')->all().
     */
    public $roles;

    /**
     * app('Metkontrol\Permission')->all().
     */
    public $permissions;

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

    public function __construct()
    {
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
}
