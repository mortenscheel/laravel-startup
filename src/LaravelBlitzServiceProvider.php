<?php

namespace MortenScheel\LaravelBlitz;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\ServiceProvider;
use MortenScheel\LaravelBlitz\Console\Commands\InstallPackagesCommand;
use MortenScheel\LaravelBlitz\Console\ConsoleOutput;

/**
 * Class LaravelBlitzServiceProvider
 * @package MortenScheel\LaravelBlitz
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 */
class LaravelBlitzServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
        $this->app->singleton(ConsoleOutput::class, ConsoleOutput::class);
        \Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $this->app->make(ConsoleOutput::class)->bind($event->output);
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-blitz.php', 'laravel-blitz');

        $this->app->singleton('laravel-blitz', function ($app) {
            return new LaravelBlitz;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-blitz'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/laravel-blitz.php' => config_path('laravel-blitz.php'),
        ], 'laravel-blitz.config');

        // Registering package commands.
        $this->commands([
            \MortenScheel\LaravelBlitz\Console\Commands\BlitzCommand::class,
        ]);
    }
}
