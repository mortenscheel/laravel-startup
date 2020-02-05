<?php

namespace MortenScheel\LaravelStartup\Packages;

use Illuminate\Contracts\Container\BindingResolutionException;
use MortenScheel\LaravelStartup\Concerns\RunsProcessCommands;
use MortenScheel\LaravelStartup\Console\ConsoleOutput;

abstract class InstallablePackage
{
    use RunsProcessCommands;

    /**
     * Semver string to install, or null for latest compatible
     * @var string|null
     */
    public $package_version = null;
    /**
     * Add as dev dependency
     * @var bool
     */
    public $require_dev = false;
    /**
     * Perform strict (slower) check to confirm package is installed
     * @var bool
     */
    public $strict_install_check = false;
    /**
     * @var ConsoleOutput
     */
    protected $output;

    public function __construct()
    {
        try {
            $this->output = app()->make(ConsoleOutput::class);
        } catch (BindingResolutionException $e) {
        }
    }

    /**
     * Override to perform custom actions after installation
     * @return bool
     */
    public function onInstalled(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        if ($this->strict_install_check) {
            return $this->runProcess([
                'composer',
                'show',
                '-q',
                $this->getPackageName()
            ]);
        }
        return \File::isDirectory(base_path('vendor/' . $this->getPackageName()));
    }

    abstract public function getPackageName(): string;

    abstract public function getPackageDescription(): string;

    protected function publishVendorByTag(string $tag): bool
    {
        return $this->runArtisanCommand('vendor:publish', [
            '--tag' => $tag
        ]);
    }

    protected function publishVendorByServiceProvider(string $service_provider): bool
    {
        return $this->runArtisanCommand('vendor:publish', [
            '--provider' => $service_provider
        ]);
    }
}
