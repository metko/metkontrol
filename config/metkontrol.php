<?php

return [

    'models' => [
        'permission' => Metko\Metkontrol\Models\Permission::class,
        'role' => Metko\Metkontrol\Models\Role::class,
        'user' => Metko\Metkontrol\Tests\User::class // Default App\User
    ],

    'table_names' => [

        'roles' => 'roles',
        'permissions' => 'permissions',  
        'permission_role' => 'permission_role',
        'role_user' => 'role_user',

    ],
];