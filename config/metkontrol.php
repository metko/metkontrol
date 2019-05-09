<?php

return [
    'models' => [
        //**
        // Permission model to use. If you changed it, you need to
        // extend your model from this one.
        //*/
        'permission' => Metko\Metkontrol\Models\Permission::class,

        //**
        // Role model to use. If you changed it, you need to
        // extend your model from this one.
        //*/
        'role' => Metko\Metkontrol\Models\Role::class,

        //**
        // User model to use. If you changed it, you need to
        // extend your model from this one.
        //*/
        'user' => App\User::class,
    ],

    'table_names' => [
        //**
        // Role table name
        //*/
        'roles' => 'mk_roles',

        //**
        // Permissions table name
        //*/
        'permissions' => 'mk_permissions',

        //**
        // Rollables table name
        // If you change it, it must be a plural version of
        // the morph fields rollable
        //*/
        'rollables' => 'mk_rollables',

        //**
        // Permissionable table name
        // If you change it, it must be a plural version
        // of the morph fields permissionable
        //*/
        'permissionables' => 'mk_permissionnables',
    ],

    'fields' => [
        //**
        // Rollables column name
        // If you change it, it must be a singular version of the morph table rollables
        //*/
        'rollable' => 'mk_rollable',

        //**
        // Permissionable column name
        // If you change it, it must be a singular version of the morph table prmissionables
        //*/
        'permissionable' => 'mk_permissionnable',
    ],

    'seeds' => [
        //**
        // Role name desired for the seed command
        //*/
        'roles' => ['Member', 'Author', 'Moderator', 'Admin', 'Super Admin'],

        //**
        // Permission name desired for the seed command
        //*/
        'permissions' => [
            'Create users', 'Update users', 'Delete users',
            'Create articles', 'Update articles', 'Delete articles',
        ],
    ],

    'cache' => [
        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         */
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        /*
         * The cache key used to store all permissions.
         */
        'key' => 'metkontrol.permission.cache',
        /*
         * When checking for a permission against a model by passing a Permission
         * instance to the check, this key determines what attribute on the
         * Permissions model is used to cache against.
         *
         * Ideally, this should match your preferred way of checking permissions, eg:
         * `$user->can('view-posts')` would be 'name'.
         */
        'model_key' => 'name',
        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */
        'store' => 'default',
    ],
];
