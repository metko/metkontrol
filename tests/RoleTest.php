<?php

use Metko\Metkontrol\Tests\User;
use Illuminate\Support\Facades\DB;
use Metko\Metkontrol\Tests\TestCase;

class RoleTest extends TestCase
{
   
   /** @test */
   public function it_has_user_models_of_the_right_class()
   {
      $this->testUser->assignRole([$this->testUserRole]);
      $this->assertCount(1, $this->testUser->roles);
      $this->assertTrue($this->testUserRole->users->first()->is($this->testUser));
      $this->assertInstanceOf(User::class, $this->testUserRole->users->first());

      $this->testCar->assignRole([$this->testUserRole2]);
      $this->assertCount(1, $this->testCar->roles);
   }

   /** @test */
   public function it_cant_assign_twice_the_same_role()
   {
       $this->testUser->assignRole([$this->testUserRole, 2]);
       $this->testUser->assignRole([$this->testUserRole]);
      $this->assertCount(2, $this->testUser->roles);
   }

   /** @test */
   public function it_cant_assign_role_by_mixed_array()
   {
      $this->testUser->assignRole([$this->testUserRole, 'testRole2']);
      $this->assertCount(2, $this->testUser->roles);
      $this->assertTrue($this->testUser->roles->first()->is($this->testUserRole));
   }

   /** @test */
   public function it_cant_assign_role_by_name()
   {
       $this->testUser->assignRole("testRole");
       $this->assertTrue($this->testUser->roles->first()->is($this->testUserRole));
   }
   
   /** @test */
   public function it_cant_assign_role_by_id()
   {
       $this->testUser->assignRole(1);
       $this->assertTrue($this->testUser->roles->first()->is($this->testUserRole));
   }

   /** @test */
   public function it_can_check_if_it_has_a_given_role()
   {
       $this->testUser->assignRole($this->testUserRole);
       $this->assertTrue($this->testUser->hasRole($this->testUserRole));
       $this->assertTrue($this->testUser->hasRole('testRole'));
       $this->assertTrue($this->testUser->hasRole(1));
   }

   /** @test */
   public function it_can_check_if_it_has_one_of_the_given_role()
   {
      $this->testUser->assignRole($this->testUserRole);
      $this->assertTrue($this->testUser->hasAnyRole(['testRole', $this->testUserRole2]));
      $this->assertTrue($this->testUser->hasAnyRole('test-role|testRole2'));
   }

   /** @test */
   public function it_can_determine_that_a_user_has_all_of_the_given_roles()
   {
      
      $this->assertFalse($this->testUser->hasAllRoles($this->roleClass->first()));
      $this->assertFalse($this->testUser->hasAllRoles('testRole'));
      $this->assertFalse($this->testUser->hasAllRoles($this->roleClass->all()));

      $this->roleClass->create(['name' => 'Second role']);
      // dd(DB::table('_mk_roles')->get());
      $this->testUser->assignRole($this->testUserRole);

      $this->assertFalse($this->testUser->hasAllRoles(['test-role', 'second-role']));

      $this->testUser->assignRole('second role');
      $this->assertTrue($this->testUser->hasAllRoles('1|second role'));
   }

   /** @test */
   public function it_can_remove_a_role()
   {
      $this->testUser->assignRole($this->testUserRole);
      $this->testUser->removeRole($this->testUserRole);
      $this->assertCount(0, $this->testUser->roles);
   }

   /** @test */
   public function it_can_remove_multiple_roles_at_the_sametime()
   {
      $this->testUser->assignRole([$this->testUserRole, $this->testUserRole2]);
      $this->testUser->removeRole([$this->testUserRole, $this->testUserRole2]);
      $this->assertCount(0, $this->testUser->roles);
   }

   /** @test */
   public function it_can_remove_all_roles()
   {
      $this->testUser->assignRole([$this->testUserRole, $this->testUserRole2]);
      $this->testUser->removeRole();
      $this->assertCount(0, $this->testUser->roles);
   }
   
}