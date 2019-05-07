<?php

namespace Metko\Metkontrol\Tests;

use Metko\Metkontrol\Tests\Car;
use Illuminate\Filesystem\Cache;
use Metko\Metkontrol\Tests\User;
use Metko\Metkontrol\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Exceptions\Handler;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;


abstract class TestCase extends Orchestra
{
   
    protected function getPackageProviders($app)
    {
        //dump('------------getPackageProviders');
        return ['Metko\Metkontrol\MetkontrolServiceProvider'];
    }

    public function setUp()
    {        
        dump('------------setup test case');

        parent::setup();

        $this->permissionClass = app('Metkontrol\Permission');
        $this->roleClass = app('Metkontrol\Role');
        $this->userClass = app('Metkontrol\User');

        $this->setUpDatabase($this->app);
        $this->testUser = User::first();
        $this->testCar = Car::find(2);

        $this->testUserRole = $this->roleClass->find(1);
        $this->testUserRole2 = $this->roleClass->find(2);
        $this->testUserPermission = $this->permissionClass->find(1);
        
        $this->testAdminRole = $this->roleClass->find(3);
        $this->testAdminPermission = $this->permissionClass->find(4);

    }
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        //dump('------------getenvironment setup');

        $app['config']->set('metkontrol', require(dirname(__DIR__).'/config/metkontrol.php'));
        $app['config']->set('view.paths', [dirname(__DIR__).'/tests/views']);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);

    }
    

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        //dump('------------setupdatabasse');
        
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(dirname(__DIR__). '/migrations');

        User::create(['name' => 'test user','email' => 'test@user.com', 'password' => 'test']);
        Car::create(['name' => 'test car','email' => 'car@car.com', 'password' => 'car']);
        
        $this->roleClass->create(['name' => 'testRole']);
        $this->roleClass->create(['name' => 'testRole2']);
        
        $this->permissionClass->create(['name' => 'Edit articles']);
        $this->permissionClass->create(['name' => 'Edit news']);
        $this->permissionClass->create(['name' => 'Edit blog']);
        $this->permissionClass->create(['name' => 'Edit comments']);
    }

}
