<?php

namespace Metko\Metkontrol;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Metko\Metkontrol\Skeleton\SkeletonClass
 */
class MetkontrolFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'metkontrol';
    }
}
