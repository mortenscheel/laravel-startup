<?php

namespace MortenScheel\PhpDependencyInstaller\Concerns;

trait DetectsOperatingSystem
{
    protected function getOperatingSystem(): string
    {
        if (\PHP_OS === 'Darwin') {
            return 'Mac';
        }
        if (\mb_stripos(\PHP_OS, 'win') !== false) {
            return 'Windows';
        }
        return 'Linux';
    }
}
