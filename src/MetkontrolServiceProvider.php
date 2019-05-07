<?php

namespace Metko\Metkontrol;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class MetkontrolServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //dump('------------register method on register');

    }

    

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Filesystem $filesystem)
    {
            //dump('------------boot method on register');
            
            $this->publishes([
                dirname(__DIR__).'/config/metkontrol.php' => config_path('metkontrol.php'),
            ], 'config');

            $this->publishes([
                dirname(__DIR__).'/seeds/MetkontrolTableSeeder.php' => $this->app->databasePath().'/seeds/MetkontrolTableSeeder.php',
            ], 'seeds');

            $this->publishes([
                dirname(__DIR__).'/migrations/create_metkontrol_tables.php' => $this->getMigrationFileName($filesystem),
            ], 'migrations');

            
            $this->registerBladeExtensions();
            $this->registerInjection();
    }

    public function registerInjection()
    {
        $config = config('metkontrol.models');
        $this->app->singleton("Metkontrol\Permission", function ($app) use($config) {
            return new $config['permission']();
        });

        $this->app->singleton("Metkontrol\Role", function ($app) use($config) {
            return new $config['role']();
        });
        $this->app->singleton("Metkontrol\User", function ($app) use($config) {
            if(app()->environment() == "testing"){
                return new \Metko\Metkontrol\Tests\User();
            }
            return new $config['user']();
        });
    }

    public function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            
            //HAS ROLE
            $bladeCompiler->directive('role', function ($role) {
                return "<?php if(auth()->check() && auth()->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('elserole', function ($role) {
                return "<?php elseif(auth()->check() && auth()->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endrole', function () {
                return '<?php endif; ?>';
            });
            //END HAS ROLE

            //UNLESS ROLE
            $bladeCompiler->directive('unlessrole', function ($role) {
                return "<?php if(!auth()->check() || ! auth()->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endunlessrole', function () {
                return '<?php endif; ?>';
            });
            //END UNLESS ROLE

            //HAS ANY ROLE
            $bladeCompiler->directive('hasanyrole', function ($roles) {
                return "<?php if(auth()->check() && auth()->user()->hasAnyRole({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function () {
                return '<?php endif; ?>';
            });
            // END HAS ANY ROLE

            // HAS ALL ROLE
            $bladeCompiler->directive('hasallroles', function ($roles) {
                return "<?php if(auth()->check() && auth()->user()->hasAllRoles({$roles})): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function () {
                return '<?php endif; ?>';
            });
            // END HAS ALL ROLE


            //HAS PERMISSIONS
            $bladeCompiler->directive('permission', function ($permission) {
                return "<?php if(auth()->check() && auth()->user()->checkPermissionTo({$permission})): ?>";
            });
            $bladeCompiler->directive('elsepermission', function ($permission) {
                return "<?php elseif(auth()->check() && auth()->user()->checkPermissionTo({$permission})): ?>";
            });
            $bladeCompiler->directive('endpermission', function () {
                return '<?php endif; ?>';
            });
            //END HAS PERMISSIONS

            //HAS ANY ROLE
            $bladeCompiler->directive('hasanypermission', function ($permissions) {
                return "<?php if(auth()->check() && auth()->user()->hasAnyPermission({$permissions})): ?>";
            });
            $bladeCompiler->directive('endhasanypermission', function () {
                return '<?php endif; ?>';
            });
            // END HAS ANY ROLE

            // HAS ALL PERMISSIONS
            $bladeCompiler->directive('hasallpermissions', function ($permissions) {
                return "<?php if(auth()->check() && auth()->user()->hasAllPermissions({$permissions})): ?>";
            });
            $bladeCompiler->directive('endhasallpermissions', function () {
                return '<?php endif; ?>';
            });
             // ENDHAS ALL PERMISSIONS

            // HAUNLESS PERMISSIONS
            $bladeCompiler->directive('unlesspermission', function ($permissions) {
                return "<?php if(auth()->check() && ! auth()->user()->hasAnyPermission({$permissions})): ?>";
            });
            $bladeCompiler->directive('endunlesspermission', function () {
                return '<?php endif; ?>';
            });
             // END UNLESS PERMISSIONS
        });
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        //dump('------------getMigrationFileName method on register');
        $timestamp = date('Y_m_d_His');
        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_metkontrol_tables.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_metkontrol_tables.php")
            ->first();
    }


    
}
