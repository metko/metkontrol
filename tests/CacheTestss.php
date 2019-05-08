<?php

namespace Metko\Metkontrol\Tests;

use Illuminate\Support\Facades\DB;
use Metko\Metkontrol\MetkontrolCache;

class CacheTest extends TestCase
{
    protected $cache_init_count = 0;
    protected $cache_load_count = 0;
    protected $cache_run_count = 2; // roles lookup, permissions lookup
    protected $cache_relations_count = 1;

    public function setUp()
    {
        parent::setUp();
        $this->registrar = app(MetkontrolCache::class);
        $this->registrar->resetCacheMetkontrol();

        DB::connection()->enableQueryLog();
    }
}
