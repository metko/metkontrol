<?php

namespace Metko\Metkontrol\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Metko\Metkontrol\Middlewares\RoleMiddleware;
use Metko\Metkontrol\Exceptions\UnauthorizedException;
use Metko\Metkontrol\Middlewares\PermissionMiddleware;
use Metko\Metkontrol\Middlewares\RoleOrPermissionMiddleware;

class MiddlewareTest extends TestCase
{
    protected $roleMiddleware;
    protected $permissionMiddleware;
    protected $roleOrPermissionMiddleware;

    public function setUp()
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->roleMiddleware = new RoleMiddleware($this->app);
        $this->permissionMiddleware = new PermissionMiddleware($this->app);
        $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware($this->app);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_role_middleware()
    {
        //$this->expectException(UnauthorizedException::class);
        //dd('ddd');
        $this->runMiddleware($this->roleMiddleware, 'testRole');
        $this->assertEquals(
           $this->runMiddleware(
               $this->roleMiddleware, 'testRole'
           ), 403);
    }

    /** @test */
    public function a_user_cant_access_a_route_protected_by_role_middleware_if_he_dont_have_one_of_the_roles()
    {
        Auth::login($this->testUser);
        $this->testUser->attachRole('testRole');
        $this->assertEquals(
           $this->runMiddleware(
               $this->roleMiddleware, 'testRole1|testRole2'
           ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_role_middleware_if_have_this_role()
    {
        Auth::login($this->testUser);
        $this->testUser->attachRole('testRole2');
        $this->assertEquals(
           $this->runMiddleware(
               $this->roleMiddleware, 'testRole|testRole2'
           ), 200);
    }

    protected function runMiddleware($middleware, $parameter)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $parameter)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_role_is_undefined()
    {
        Auth::login($this->testUser);
        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, ''
            ), 403);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_middleware_if_have_this_permission()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_permission_middleware_if_have_one_of_the_permissions()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-news|edit-articles'
            ), 200);
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, ['edit-news', 'edit-articles']
            ), 200);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_if_have_a_different_permission()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-news'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_permission_middleware_if_have_not_permissions()
    {
        Auth::login($this->testUser);
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles|edit-news'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_or_role_middleware_if_has_this_permission_or_role()
    {
        Auth::login($this->testUser);
        $this->testUser->attachRole('testRole');
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-news|edit-articles'),
            200
        );
        $this->testUser->removeRole('testRole');
        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'),
            200
        );
        $this->testUser->removePermissionTo('edit-articles');
        $this->testUser->attachRole('testRole');
        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'),
            200
        );
        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, ['testRole', 'edit-articles']),
            200
        );
    }

    /** @test */
    public function a_user_can_not_access_a_route_protected_by_permission_or_role_middleware_if_have_not_this_permission_and_role()
    {
        Auth::login($this->testUser);
        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'),
            403
        );
    }
}
