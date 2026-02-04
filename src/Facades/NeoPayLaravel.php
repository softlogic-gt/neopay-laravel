<?php
namespace SoftlogicGT\NeoPayLaravel\Facades;

use Illuminate\Support\Facades\Facade;

class NeoPayLaravel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'neopay-laravel';
    }
}
