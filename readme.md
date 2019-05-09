
[![Latest Version on Packagist](https://img.shields.io/packagist/v/metko/metkontrol.svg?style=flat-square)](https://packagist.org/packages/metko/metkontrol)

[![Build Status](https://img.shields.io/travis/metko/metkontrol/master.svg?style=flat-square)](https://travis-ci.org/metko/metkontrol)

[![Quality Score](https://img.shields.io/scrutinizer/g/metko/metkontrol.svg?style=flat-square)](https://scrutinizer-ci.com/g/metko/metkontrol)

[![Total Downloads](https://img.shields.io/packagist/dt/metko/metkontrol.svg?style=flat-square)](https://packagist.org/packages/metko/metkontrol)

  

Simple personal package to handle roles & permissions inside a Laravel app. Compatible with Laravel 5.8.
  
  

# Installation
 
Via composer:
```bash

composer require metko/metkontrol

```


Deploy the config & migrations file

```bash

php artisan vendor:publish --provider="Metko\Metkontrol\MetkontrolServiceProvider"

```

And finaly make the migration

```bash

php artisan migrate

```

___

> Optionally, you can specify the firsts roles and permissions you want
> in the config file. By default, it will use the following ones :
> 

```php

'roles'  =>  ['Member',  'Author',  'Moderator',  'Admin',  'Super Admin'],

'permissions'  =>  ['A permission',  'Second permission',  'third permission'],

```

> Blockquote

Duplicate the seeder class file

```bash

php artisan vendor:publish --provider="Metko\Metkontrol\MetkontrolServiceProvider" --tag="seeds"

```


Run composer auto-load

```bash

composer dump-autoload

```

And seed!

```bash

php artisan db:seed --class=MetkontrolTableSeeder

```

  

# Usage

Just add the traits to the model you want to have role and permissions. (Note that you can use only the Role if you want, but not the opposite).

```php
use Metko\Metkontrol\MetkontrolRole,
	Metko\Metkontrol\MetkontrolPermission,
	Metko\Metkontrol\MetkontrolCache; // To update the cache when a model instance  is created or updated
```
  

## Roles

  

### Attach a  roles to a model

``` php

$role = Role::find(1);

$model->attachRole($role); // Role class

// or

$model->attachRole(1); // Role ID

$model->attachRole('Author')  // Role Name or slug

// You can also pass an array

$model->attachRole([$role,  1,  'author']);

// Or a piped string

$model->attachRole("role-name|2|moderator|Super admin");

```

  

### Remove roles

``` php

$role  =  Role::find(1);

$model->removeRole($role);

// or

$model->removeRole(1);

$model->removeRole('role name')  // Name or slug

// or

$model->removeRole([$role,  1,  'role name']);

$model->removeRole("1|Moderator");

// or

$model->removeRole();  // Will remove all the role

```


### Check if the model has a specific role

``` php

$role  =  Role::find(1);

$model->attachRole($role); 


$model->hasRole($role);  // True

// or

$model->hasRole(1);  // True
$model->hasRole("Author");  // True
$model->hasRole("author");  // True

$model->hasRole('wrong role name')  // False

```
### Check if the model has one of the role

 ```php
 
 $model->hasAnyRole('test|3|a-slugged-role');
 
 ```
 
### Check if the model has all the given role

 ```php
 
 $model->hasAllRoles([$role, 3, "A new role"]);
 
 ```


## Permissions

  

### Give permissions to a moddel

  

``` php

$permission  =  Permission::find(1);

$model->givePermissionTo($permission);

```

> Like for the role, you can p.ass an ID, a name, a slug, an array of mixed elements (string, model, int) or a piped string

  

### Revoke permissions

  

``` php

$model->revokePermission('permission name')  // Name or slug

// or

$model->revokePermission('permission1 name|permission2 slug');

$model->revokePermission([$permission1,  $permission2]);

$model->revokePermission(); // Will remove all the permission


```

  

### Check if it has a specific permission

  

``` php

$model->hasAnyPermission($permission1)  // Return bool

```

Or

``` php

$model->hasPermissionTo($permission1)  // Return bool

```

If it has one of the permissions

``` php

$model->hasAnyPermission("permission1|another-one|4")  // Return bool

```

If it has all the permission

``` php

$model->hasAllPermissions([$permission1, "second-permission"])  // Return bool

```

You can also check if the model have a specific permission directly from the permission model or the role model 

``` php

$model->hasDirectPermission($permission1)  // Return bool

$model->hasDirectPermissionViaRole($permission1)  // Return bool


```

## Middlewares

You can use the middleware to check if the user has the given roles

```php

Route::get('/', 'homeController@index')->middleware('hasRole:author');
Route::get('/', 'homeController@index')->middleware('hasRole:author|writer');

```

Or a permission

```php

Route::get('/', 'homeController@index')->middleware('hasPermission:delete-account');
Route::get('/', 'homeController@index')->middleware('hasPermission:create-news|edit-blog');

```

## Blades

You can use blade template to check if the user has a role

```php

@role('author')
// Only author can see it
@elserole('member')
// Only member can see it
@endrole

```

You can use blade template to check if the user has a one of the roles

```php

@hasanyrole('author|3') // you can also pass a array 
// Only author and role with ID to 3 can see it
@endhasanyrole

```

You can use blade template to check if the user has all the role

```php

@hasallrole('author|3') // you can also pass a array 
// Only author and role with ID to 3 can see it
@endhasallrole

```

You can use blade template to check if the user has all the role

```php

@unlessrole(1) // you can also pass a array 
// People without the role 1 can see this line
@endunlessrole

```

Or a permission

```php

Route::get('/', 'homeController@index')->middleware('hasPermission:delete-account');
Route::get('/', 'homeController@index')->middleware('hasPermission:create-news|edit-blog');

```

Or both

```php

Route::get('/', 'homeController@index')->middleware('hasRoleOrPermission:delete-account');
Route::get('/', 'homeController@index')->middleware('hasRoleOrPermission:create-news|Author');

```

### Testing

  

``` bash

composer test

```

  

### Changelog

  

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

  

## Contributing

  

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

  

### Security

  

If you discover any security related issues, please email metko@gmail.com instead of using the issue tracker.

  

## Credits

  

-  [Metko](https://github.com/metko)

-  [All Contributors](../../contributors)

  

## License

  

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
