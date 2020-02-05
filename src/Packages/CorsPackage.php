<?php

namespace MortenScheel\LaravelStartup\Packages;

use MortenScheel\LaravelStartup\Actions\FileManipulation\AppendPhpArray;

class CorsPackage extends InstallablePackage
{
    public function onInstalled(): bool
    {
        $this->publishVendorByTag('cors');
        return (new AppendPhpArray([
            'file_path' => app_path('Http/Kernel.php'),
            'variable_name' => '$middleware',
            'value' => '\Fruitcake\Cors\HandleCors::class'
        ]))->run();
    }

    public function getPackageName(): string
    {
        return 'fruitcake/laravel-cors';
    }

    public function getPackageDescription(): string
    {
        return 'Handles Cross-Origin request headers';
    }
}
