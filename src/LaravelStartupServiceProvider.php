<?php

namespace MortenScheel\LaravelStartup;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\ServiceProvider;
use MortenScheel\LaravelStartup\Console\Commands\InstallPackagesCommand;
use MortenScheel\LaravelStartup\Console\ConsoleOutput;

/**
 * Class LaravelStartupServiceProvider
 * @package MortenScheel\LaravelStartup
 *
 * @property \Illuminate\Contracts\Foundation\Application $app
 */
class LaravelStartupServiceProvider extends ServiceProvider
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
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-startup.php', 'laravel-startup');

        $this->app->singleton('laravel-startup', function ($app) {
            return new LaravelStartup;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-startup'];
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
            __DIR__ . '/../config/laravel-startup.php' => config_path('laravel-startup.php'),
        ], 'laravel-startup.config');

        // Registering package commands.
        $this->commands([
            \MortenScheel\LaravelStartup\Console\Commands\InstallPhpCsFixerCommand::class,
            \MortenScheel\LaravelStartup\Console\Commands\StartupCommand::class,
        ]);
    }
}
