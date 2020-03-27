<?php

namespace MortenScheel\PhpDependencyInstaller;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{
    public function put(string $path, string $contents)
    {
        return \file_put_contents($this->getAbsolutePath($path), $contents);
    }

    public function getAbsolutePath(string $path)
    {
        if (\mb_strpos($path, '/') === 0) {
            return $path; // UNIX
        }
        if (\preg_match('~^[a-z]:~i', $path)) {
            return $path; // Windows
        }
        if (\mb_stripos($path, '~') === 0) {
            return \getenv('HOME') . \mb_substr($path, 1);
        }
        return \getcwd() . \DIRECTORY_SEPARATOR . $path;
    }

    public function get(string $path)
    {
        return \file_get_contents($this->getAbsolutePath($path));
    }

    public function getGlobalConfigPath(string $path = null)
    {
        if (OS::detect() === OS::WINDOWS) {
            $root = \getenv('APPDATA') . '\PhpDependencyInstaller';
        } else {
            $root = \getenv('HOME') . '/.config/PhpDependencyInstaller';
        }
        return $path ? ($root . \DIRECTORY_SEPARATOR . $path) : $root;
    }
}
