<?php
namespace SoftlogicGT\NeoPayLaravel;

use Illuminate\Routing\Router;
use SoftlogicGT\NeoPayLaravel\NeoPay;
use Illuminate\Support\ServiceProvider;

class NeoPayServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(Router $router)
    {
        $this->mergeConfigFrom(__DIR__ . '/config/neopay.php', 'neopay');
        $this->loadViewsFrom(__DIR__ . '/resources/views/', 'neopay-laravel');
        $this->publishes([
            __DIR__ . '/config/neopay.php' => config_path('neopay.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton('neopay-laravel', function ($app) {
            return new NeoPay;
        });
    }

    public function provides()
    {
        return ['neopay-laravel'];
    }
}
