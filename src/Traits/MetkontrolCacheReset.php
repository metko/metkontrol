<?php

namespace Metko\Metkontrol\Traits;

use Metko\Metkontrol\MetkontrolCache;

trait MetkontrolCacheReset
{
    public static function bootMetkontrolCacheReset()
    {
        static::saved(function () {
            app(MetkontrolCache::class)->resetCacheMetkontrol();
        });
        static::deleted(function () {
            app(MetkontrolCache::class)->resetCacheMetkontrol();
        });
    }
}
