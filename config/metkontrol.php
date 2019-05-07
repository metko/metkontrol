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
        'user' => App\User::class 
    ],

    'table_names' => [

        //** 
        // Role table name 
        //*/
        'roles' => '_mk_roles',

        //** 
        // Permissions table name 
        //*/
        'permissions' => '_mk_permissions',
        
        //** 
        // Rollables table name
        // If you change it, it must be a plural version of 
        // the morph fields rollable 
        //*/
        'rollables' => '_mk_role_subjects', 

        //** 
        // Permissionable table name
        // If you change it, it must be a plural version 
        // of the morph fields permissionable 
        //*/
        'permissionables' => '_mk_permission_subjects', 

    ],

    'fields' => [

        //** 
        // Rollables column name
        // If you change it, it must be a singular version of the morph table rollables 
        //*/
        'rollable' => '_mk_role_subject',

        //** 
        // Permissionable column name
        // If you change it, it must be a singular version of the morph table prmissionables 
        //*/
        'permissionable' => '_mk_permission_subject',
    ],

    'seeds' => [

        //** 
        // Role name desired for the seed command
        //*/
        'roles' => [ 'Member', 'Author', 'Moderator', 'Admin', 'Super Admin'],

        //** 
        // Permission name desired for the seed command
        //*/
        'permissions' => ['Edit articles', 'Create articles', 'Delete articles']

    ]
];