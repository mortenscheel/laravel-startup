<?php

namespace MortenScheel\LaravelBlitz\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelBlitz extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-blitz';
    }
}
