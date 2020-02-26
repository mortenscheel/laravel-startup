<?php

namespace MortenScheel\PhpDependencyInstaller;

class OS
{
    public const MAC = 'mac';
    public const LINUX = 'linux';
    public const WINDOWS = 'windows';

    public static function detect(): string
    {
        if (\PHP_OS === 'Darwin') {
            return self::MAC;
        }
        if (\mb_stripos(\PHP_OS, 'win') !== false) {
            return self::WINDOWS;
        }
        return self::LINUX;
    }
}
