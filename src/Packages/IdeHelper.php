<?php


namespace MortenScheel\LaravelStartup\Packages;

class IdeHelper extends InstallablePackage
{
    public function getPackageName(): string
    {
        return 'barryvdh/laravel-ide-helper';
    }

    public function getPackageDescription(): string
    {
        return 'Generates IDE helper files';
    }

    public function onInstalled(): bool
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        return $this->publishVendorByServiceProvider(
            'Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider'
        );
    }
}
