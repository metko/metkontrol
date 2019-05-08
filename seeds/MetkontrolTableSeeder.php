<?php

use Illuminate\Database\Seeder;
use Metko\Metkontrol\Models\Role;
use Metko\Metkontrol\Models\Permission;

class MetkontrolTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        foreach (config('metkontrol.seeds.roles') as $role) {
            Role::create(['name' => $role]);
        }

        foreach (config('metkontrol.seeds.permissions') as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
