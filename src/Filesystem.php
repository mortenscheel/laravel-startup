<?php

namespace MortenScheel\PhpDependencyInstaller;

use MortenScheel\PhpDependencyInstaller\Concerns\RunsShellCommands;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{
    use RunsShellCommands;

    public function put(string $path, string $contents)
    {
        return \file_put_contents($this->getAbsolutePath($path), $contents);
    }

    public function get(string $path)
    {
        return \file_get_contents($this->getAbsolutePath($path));
    }

    public function getGlobalConfigPath(string $path = null)
    {
        $os = \mb_strtolower(\PHP_OS);
        if ($os !== 'darwin' && \mb_strpos($os, 'win') !== false) {
            $root = getenv('APPDATA') . '\PhpDependencyInstaller';
        } else {
            $root = getenv('HOME') . '/.config/PhpDependencyInstaller';
        }
        return $path ? ($root . DIRECTORY_SEPARATOR . $path) : $root;
    }

    public function getPresetFiles()
    {
        if ($this->exists($this->getGlobalConfigPath('presets'))) {
            return collect(scandir($this->getGlobalConfigPath('presets')))->filter(function ($path) {
                return preg_match('/\.yml$/', $path);
            })->mapWithKeys(function ($path) {
                preg_match('/(.*)\.yml$/', $path, $match);
                return [$match[1] => $this->getGlobalConfigPath('presets') . DIRECTORY_SEPARATOR . $path];
            });
        }
    }


    public function getAbsolutePath(string $path)
    {
        if (\mb_strpos($path, '/') === 0) {
            return $path; // UNIX
        }
        if (\preg_match('~^[a-z]:]~i', $path)) {
            return $path; // Windows
        }
        return \getcwd() . \DIRECTORY_SEPARATOR . $path;
    }
}
