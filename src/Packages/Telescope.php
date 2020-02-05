<?php


namespace MortenScheel\LaravelStartup\Packages;

class Telescope extends InstallablePackage
{
    public function getPackageName(): string
    {
        return 'laravel/telescope';
    }

    public function getPackageDescription(): string
    {
        return 'Application monitoring';
    }

    public function onInstalled(): bool
    {

    }
}
