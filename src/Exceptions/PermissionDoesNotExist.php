<?php
namespace Metko\Metkontrol\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function named(string $roleName)
    {
        return new static("There is no permission named `{$roleName}`.");
    }

    public static function withId(int $roleId)
    {
        return new static("There is no permission with id `{$roleId}`.");
    }

    public static function withType($type = null)
    {
        return new static("{$type} is not a valid permission class");
    }
    
    
}