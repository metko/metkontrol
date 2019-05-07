[![Latest Version on Packagist](https://img.shields.io/packagist/v/metko/metkontrol.svg?style=flat-square)](https://packagist.org/packages/metko/metkontrol)
[![Build Status](https://img.shields.io/travis/metko/metkontrol/master.svg?style=flat-square)](https://travis-ci.org/metko/metkontrol)
[![Quality Score](https://img.shields.io/scrutinizer/g/metko/metkontrol.svg?style=flat-square)](https://scrutinizer-ci.com/g/metko/metkontrol)
[![Total Downloads](https://img.shields.io/packagist/dt/metko/metkontrol.svg?style=flat-square)](https://packagist.org/packages/metko/metkontrol)

Package inspired by Spatie/Laravel-permission for simple roles & permission managment.


# Installation

You can install the package via composer:

```bash
composer require metko/metkontrol
```

# Usage

## Roles

### Assign roles
``` php
$model = Role::find(1);
$model->assignRole($role);
// or
$model->assignRole(1);
$model->assignRole('role name') // Name or slug
// or
$model->assignRole([$role, 1, 'role name']);
```

### Remove roles
``` php
$role = Role::find(1);
$model->removeRole($role);
// or
$model->removeRole(1);
$model->removeRole('role name') // Name or slug
// or
$model->removeRole([$role, 1, 'role name']);
// or
$model->removeRole(); // Will remove all the role
```

### Check if the model has a specific role
``` php
$role = Role::find(1);
$model->hasRole($role); // True
// or
$model->hasRole(1); // True
$model->hasRole('wrong role name') // False
```

### Check if the model has a specific permi
``` php
$role = Role::find(1);
$model->hasRole($role); // True
// or
$model->hasRole(1); // True
$model->hasRole('wrong role name') // False
```

## Permissions

### Give permissions

``` php
$model = User::find(1);
$permission = Permission::find(1);
$model->givePermissionTo($permission);
// or
$model->givePermissionTo(1);
$model->givePermissionTo('permission name') // Name or slug
// or
$model->givePermissionTo([$role, 1, 'permission name']);
```

### Revoke permissions

``` php
$model = Role::find(1);
$permission1 = Permission::find(1);
$permission2 = Permission::find(2);
$model->givePermissionTo($permission1);
$model->givePermissionTo($permission2);
// or
$model->revokePermission('permission name') // Name or slug
// or
$model->revokePermission('permission1 name|permission2 slug');
$model->revokePermission([$permission1, $permission2]);
```

### Check

``` php
$model = Role::find(1);
$permission1 = Permission::find(1);
$permission2 = Permission::find(2);
$model->givePermissionTo($permission1);
$model->givePermissionTo($permission2);
// or
$model->hasPermissionTo($permission1) // Name or slug // Return bool
// or
$model->hasAllPermission([$permission1, $permission2]);
$model->hasAnyPermission('permission1slug|permission2slug');
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

- [Metko](https://github.com/metko)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).