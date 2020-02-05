<?php

namespace MortenScheel\LaravelStartup\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelStartup extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-startup';
    }
}
