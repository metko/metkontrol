<?php
namespace Metko\Metkontrol\Tests;

use Illuminate\Support\Facades\DB;
use Metko\Metkontrol\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class BladeTest extends TestCase
{
   public function setup()
   {
      parent::setup();

      $this->roleClass->create(['name' => 'member']);
      $this->roleClass->create(['name' => 'writer']);
   }

   /** @test */
   public function all_blade_directives_will_evaluate_falsly_when_there_is_nobody_logged_in()
   {
      $role = "writer";
      $otherrole = "moderator";
      $this->assertEquals('does not have role', $this->renderView('role', compact('role', 'otherrole')));
   }

   /** @test */
   public function all_blade_directives_will_evaluate_falsy_when_somebody_without_roles_or_permissions_is_logged_in()
   {
      $role = "writer";
      $otherrole = "moderator";
      auth()->setUser($this->testUser);
      $this->assertEquals('does not have role', $this->renderView('role', compact('role', 'otherrole')));
   }

   /** @test */
   public function the_role_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('has role', $this->renderView('role', ['role' => 'writer', 'otherrole' => 'na']));
   }

   /** @test */
   public function the_elserole_directive_will_evaluate_true_when_the_logged_in_user_has_the_role()
   {
       auth()->setUser($this->getMember());
       $this->assertEquals('has else role', $this->renderView('role', ['role' => 'writer', 'otherrole' => 'member']));
   }

   /** @test */
   public function the_unlessrole_directive_will_evaluate_true_when_the_logged_in_user_does_not_have_the_role()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('does not have role', $this->renderView('unlessrole', ['role' => 'another']));
   }

   /** @test */
   public function the_unlessrole_directive_will_evaluate_false_when_the_logged_in_user_have_the_role()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('has role', $this->renderView('unlessrole', ['role' => 'writer']));
   }

   /** @test */
   public function the_hasanyrole_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_any_of_the_required_roles()
   {
       $roles = ['writer', 'intern'];
       auth()->setUser($this->getMember());
       $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', compact('roles')));
       $this->assertEquals('does not have any of the given roles', $this->renderView('hasAnyRole', ['roles' => implode('|', $roles)]));
   }
   /** @test */
   public function the_hasanyrole_directive_will_evaluate_true_when_the_logged_in_user_does_not_have_any_of_the_required_roles()
   {
       $roles = ['writer', 'intern'];
       auth()->setUser($this->getWriter());
       $this->assertEquals('does have some of the roles', $this->renderView('hasAnyRole', compact('roles')));
       $this->assertEquals('does have some of the roles', $this->renderView('hasAnyRole', ['roles' => implode('|', $roles)]));
   }

   /** @test */
   public function the_hasallroles_directive_will_evaluate_false_when_the_logged_in_user_does_not_have_all_required_roles()
   {
       $roles = ['member', 'writer'];
       auth()->setUser($this->getMember());
       $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
       $this->assertEquals('does not have all of the given roles', $this->renderView('hasAllRoles', ['roles' => implode('|', $roles)]));
   }
   /** @test */
   public function the_hasallroles_directive_will_evaluate_true_when_the_logged_in_user_have_all_required_roles()
   {
       $roles = ['member', 'writer'];
       auth()->setUser($this->getMember());
       $this->testUser->assignRole('writer');
       $this->assertEquals('does have all of the given roles', $this->renderView('hasAllRoles', compact('roles')));
       $this->assertEquals('does have all of the given roles', $this->renderView('hasAllRoles', ['roles' => implode('|', $roles)]));
   }

   /** @test */
   public function the_permission_directive_will_evaluate_true_when_the_logged_in_user_has_the_permission()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('has permission', $this->renderView('permission', [
           'permission' => 'edit-articles', 
           'otherpermission' => 'ds']));
   }

   /** @test */
   public function the_permission_directive_will_evaluate_false_when_the_logged_in_user_doesnt_has_the_permission()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('does not have permission', $this->renderView('permission', [
           'permission' => 'edit-blog', 'otherpermission' => 'ds'
        ]));
   }

   /** @test */
   public function the_permission_directive_will_evaluate_true_when_the_logged_in_user_has_the_other_permission()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('has otherpermission', $this->renderView('permission', [
           'permission' => 'edit-blog', 'otherpermission' => 'edit-articles'
           ]));
   }

   /** @test */
   public function the_hasanypermission_directive_will_evaluate_true_when_the_logged_in_user_has_one_of_the_permission()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('does have some of the permissions', $this->renderView('hasAnyPermission', [
           'permissions' => 'edit-blog|edit-articles'
        ]));
   }
   /** @test */
   public function the_hasallpermission_directive_will_evaluate_true_when_the_logged_in_user_has_all_of_the_permission()
   {
       auth()->setUser($this->getWriter());
       $this->testUser->givePermissionTo('edit-blog');
       $this->assertEquals('does have all of the given permissions', $this->renderView('hasAllPermissions', [
           'permissions' => 'edit-blog|edit-articles'
        ]));
   }

   /** @test */
   public function the_hasallpermission_directive_will_evaluate_false_when_the_logged_in_user_doesnt_has_all_of_the_permission()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('does not have all of the given permissions', $this->renderView('hasAllPermissions', [
           'permissions' => 'edit-blog|edit-articles'
        ]));
   }

   /** @test */
   public function the_unlesspermission_directive_will_evaluate_true_when_the_logged_in_user_doesnt_has_the_permission()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('does not have permission', $this->renderView('unlessPermission', [
           'permission' => 'edit-blog|edit-news'
        ]));
   }

   /** @test */
   public function the_unlesspermission_directive_will_evaluate_false_when_the_logged_in_user_has_the_permission()
   {
       auth()->setUser($this->getWriter());
       $this->assertEquals('has permission', $this->renderView('unlessPermission', [
           'permission' => 'edit-articles'
        ]));
   }

   public function getWriter()
   {
      $this->testUser->assignRole('writer');
      $this->testUser->givePermissionTo('edit-articles');
      return $this->testUser;
   }

   public function getMember()
   {
      $this->testUser->assignRole('member');
      return $this->testUser;
   }

   protected function renderView($view, $parameters)
    {
        Artisan::call('view:clear');
        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }
        return trim((string) ($view));
    }

}