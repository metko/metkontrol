<?php

return [

    'models' => [

        'permission' => Metko\Metkontrol\Models\Permission::class,

        'role' => Metko\Metkontrol\Models\Role::class,

        'user' => App\User::class 
    ],

    'table_names' => [

        'roles' => '_mk_roles',

        'permissions' => '_mk_permissions',
        
        'rollables' => '_mk_role_subjects', // Just add an "s" to the end for the Laravel convention

        'permissionables' => '_mk_permission_subjects', // Just add an "s" to the end for the Laravel convention

    ],
    'fields' => [

        'rollable' => '_mk_role_subject',

        'permissionable' => '_mk_permission_subject',

    ]
];