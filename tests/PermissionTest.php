<?php

namespace Metko\Metkontrol\Tests;

use Illuminate\Support\Facades\DB;
use Metko\Metkontrol\Exceptions\PermissionDoesNotExist;
use Metko\Metkontrol\Exceptions\PermissionAlreadyExists;

class PermissionTest extends TestCase
{
    public function setup()
    {
        parent::setup();
    }

    protected function createUser($name)
    {
        return $this->userClass->create([
            'name' => $name, 'email' => "{$name}@test.com",
            'password' => 'pass',
            ]);
    }

    /** @test */
    public function it_has_user_models_of_the_right_class()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);
        $this->assertCount(1, $this->testUserPermission->users);
        $this->assertTrue($this->testUserPermission->users->first()->is($this->testUser));
        $this->assertInstanceOf(get_class($this->userClass), $this->testUserPermission->users->first());
    }

    /** @test */
    public function it_can_assign_a_permission_to_a_user()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);
        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testUser->givePermissionTo('permission-does-not-exist');
    }

    /** @test */
    public function it_can_remove_a_permission_from_a_model()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);
        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));
        $this->testUser->removePermissionTo($this->testUserPermission);
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = $this->createUser('jean');
        $user2 = $this->createUser('edouard');

        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');

        $user2->attachRole('testRole');
        //dd(DB::table('permissionables')->get());
        $scopedUsers1 = $this->userClass->permission('edit-articles')->get();
        $scopedUsers2 = $this->userClass->permission(['edit-news'])->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_an_array()
    {
        $user1 = $this->createUser('jean');
        $user2 = $this->createUser('edouard');

        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles|edit-blog');
        $user2->attachRole('testRole');

        $scopedUsers1 = $this->userClass->permission(['edit-articles', 'edit-news'])->get();
        $scopedUsers2 = $this->userClass->permission('edit-news|edit-blog')->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_a_collection()
    {
        $user1 = $this->createUser('jean');
        $user2 = $this->createUser('edouard');

        $user1->givePermissionTo(['edit-articles', 'edit-news']);
        $this->testUserRole->givePermissionTo('edit-articles');
        $user2->attachRole('testRole');
        $scopedUsers1 = $this->userClass->permission(collect(['edit-articles', 'edit-news']))->get();
        $scopedUsers2 = $this->userClass->permission(collect(['edit-news']))->get();

        $this->assertEquals($scopedUsers1->count(), 2);
        $this->assertEquals($scopedUsers2->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = $this->createUser('jean');
        $user1->givePermissionTo($this->testUserPermission->name);

        $scopedUsers1 = $this->userClass->permission($this->testUserPermission)->get();
        $scopedUsers2 = $this->userClass->permission([$this->testUserPermission])->get();
        $scopedUsers3 = $this->userClass->permission(collect([$this->testUserPermission]))->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 1);
        $this->assertEquals($scopedUsers3->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_without_permissions_only_role()
    {
        $user1 = $this->createUser('jean');
        $user2 = $this->createUser('edouard');

        $this->testUserRole->givePermissionTo('edit-articles');
        $user1->attachRole($this->testUserRole);
        $user2->attachRole($this->testUserRole);
        $scopedUsers = $this->userClass->permission('edit-articles')->get();
        $this->assertEquals($scopedUsers->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_without_roles_only_permission()
    {
        $user1 = $this->createUser('jean');
        $user2 = $this->createUser('edouard');

        $user1->givePermissionTo(['edit-news']);
        $user2->givePermissionTo(['edit-articles', 'edit-news']);

        $scopedUsers = $this->userClass->permission('edit-news')->get();
        $this->assertEquals($scopedUsers->count(), 2);
    }

    /** @test */
    public function it_can_give_and_remove_multiple_permissions()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);
        $this->assertEquals(2, $this->testUserRole->permissions()->count());
        $this->testUserRole->removePermissionTo('edit-articles|edit-news');
        $this->assertEquals(0, $this->testUserRole->permissions()->count());
        $this->testUserRole->givePermissionTo('edit-articles|edit-news');
        $this->assertEquals(2, $this->testUserRole->permissions()->count());
        $this->testUserRole->removePermissionTo([$this->testUserPermission, 'edit-news']);
        $this->assertEquals(0, $this->testUserRole->permissions()->count());
    }

    /** @test */
    public function it_can_remove_all_permissions()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);
        $this->assertEquals(2, $this->testUserRole->permissions()->count());
        $this->testUserRole->removePermissionTo();
        $this->assertEquals(0, $this->testUserRole->permissions()->count());
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $this->testUser->hasPermissionTo('does-not-exist');
    }

    /** @test */
    public function it_can_work_with_a_user_that_does_not_have_any_permissions_at_all()
    {
        $this->assertFalse($this->userClass->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly()
    {
        $this->assertFalse($this->testUser->hasAnyPermission('edit-articles'));
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));
        $this->testUser->givePermissionTo('edit-news');
        $this->testUser->removePermissionTo($this->testUserPermission);
        $this->assertTrue($this->testUser->hasAnyPermission('edit-articles|edit-news'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAnyPermission(['edit-articles']));
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));
        $this->testUser->givePermissionTo('edit-news');
        $this->testUser->removePermissionTo($this->testUserPermission);
        $this->assertTrue($this->testUser->hasAnyPermission(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_via_role()
    {
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->attachRole('testRole');
        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_all_of_the_permissions_directly()
    {
        $this->testUser->givePermissionTo('edit-articles|edit-news');
        $this->assertTrue($this->testUser->hasAllPermissions('edit-articles|edit-news'));
        $this->testUser->removePermissionTo('edit-articles');
        $this->assertFalse($this->testUser->hasAllPermissions('edit-articles', 'edit-news'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_all_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']));
        $this->testUser->removePermissionTo('edit-articles');
        $this->assertFalse($this->testUser->hasAllPermissions(['edit-news', 'edit-articles']));
        $this->testUser->givePermissionTo('edit-news');
        $this->testUser->removePermissionTo($this->testUserPermission);
        $this->assertFalse($this->testUser->hasAllPermissions(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_all_of_the_permissions_via_role()
    {
        $this->testUserRole->givePermissionTo('edit-articles', 'edit-news');
        $this->testUser->attachRole('testRole');
        $this->assertTrue($this->testUser->hasAllPermissions('edit-articles', 'edit-news'));
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_an_invalid_type()
    {
        $user1 = $this->createUser('jean');
        $this->expectException(PermissionDoesNotExist::class);
        $user1->hasPermissionTo(new \stdClass());
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasPermissionTo_with_null()
    {
        $user1 = $this->createUser('jean');
        $this->expectException(PermissionDoesNotExist::class);
        $user1->hasPermissionTo(null);
    }

    /** @test */
    public function it_can_determine_that_user_has_direct_permission()
    {
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));
        $this->assertEquals(
           collect(['edit-articles']),
           $this->testUser->getDirectPermissions()->pluck('slug')
       );
        $this->testUser->removePermissionTo('edit-articles');
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));
        $this->testUser->attachRole('testRole');
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));
    }

    /** @test */
    public function it_can_list_all_the_permissions_via_roles_of_user()
    {
        $this->testUserRole2->givePermissionTo('edit-news');
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->attachRole('testRole|testRole2');
        $this->assertEquals(
           collect(['edit-articles', 'edit-news']),
           $this->testUser->getPermissionsViaRoles()->pluck('slug')
       );
    }

    /** @test */
    public function it_can_list_all_the_coupled_permissions_both_directly_and_via_roles()
    {
        $this->testUser->givePermissionTo('edit-news');
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->attachRole('testRole');
        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getAllPermissions()->pluck('slug')
        );
    }

    /** @test */
    public function it_does_not_remove_already_associated_permissions_when_assigning_new_permissions()
    {
        $this->testUser->givePermissionTo('edit-news');
        $this->testUser->givePermissionTo('edit-articles');
        $this->assertTrue($this->testUser->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function it_does_not_throw_an_exception_when_assigning_a_permission_that_is_already_assigned()
    {
        $this->testUser->givePermissionTo('edit-news');
        $this->testUser->givePermissionTo('edit-news');
        $this->assertTrue($this->testUser->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function it_can_retrieve_permission_slug()
    {
        $this->testUser->givePermissionTo('edit-news|edit-articles');
        $this->assertEquals(
            collect(['edit-news', 'edit-articles']),
            $this->testUser->getPermissionNames('slug')
        );
    }

    /** @test */
    public function it_can_retrieve_permission_name()
    {
        $this->testUser->givePermissionTo('edit-news|edit-articles');
        $this->assertEquals(
            collect(['Edit news', 'Edit articles']),
            $this->testUser->getPermissionNames()
        );
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasDirectPermission_with_an_invalid_type()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $user1 = $this->createUser('jean');
        $this->assertFalse($user1->hasDirectPermission(new \stdClass()));
    }

    /** @test */
    public function it_throws_an_exception_when_calling_hasDirectPermission_with_null()
    {
        $this->expectException(PermissionDoesNotExist::class);
        $user1 = $this->createUser('jean');
        $this->assertFalse($user1->hasDirectPermission(null));
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_already_exists()
    {
        $this->expectException(PermissionAlreadyExists::class);
        $this->permissionClass->create(['name' => 'test-permission']);
        $this->permissionClass->create(['name' => 'test-permission']);
    }

    /** @test */
    public function it_is_retrievable_by_id()
    {
        $permission_by_id = $this->permissionClass->findById($this->testUserPermission->id);
        $this->assertEquals($this->testUserPermission->id, $permission_by_id->id);
    }
}
