<?php

namespace MortenScheel\PhpDependencyInstaller;

class Filesystem extends \Symfony\Component\Filesystem\Filesystem
{
    public function put(string $path, string $contents)
    {
        return \file_put_contents($this->getAbsolutePath($path), $contents);
    }

    public function get(string $path)
    {
        return \file_get_contents($this->getAbsolutePath($path));
    }

    public function hasGlobalConfig()
    {
        return $this->exists($this->getGlobalConfigFilePath());
    }

    private function getGlobalConfigFolder()
    {
        $os = \mb_strtolower(\PHP_OS);
        if (\mb_strpos($os, 'win') !== false) {
            return '%APPDATA%/PhpDependencyInstaller';
        }
        return '$HOME/.config/PhpDependencyInstaller';
    }

    public function getGlobalConfigFilePath()
    {
        return \sprintf('%s/preset-template.yml', $this->getGlobalConfigFolder());
    }

    public function getConfig()
    {
        return $this->get($this->getGlobalConfigFilePath());
    }

    /**
     * @return string|null
     */
    public function createConfig()
    {
        if (!$this->exists($this->getGlobalConfigFolder())) {
            $this->mkdir($this->getGlobalConfigFolder(), 0755);
        }
        if (!$this->exists($this->getGlobalConfigFilePath())) {
            $this->copy(__DIR__ . '/../config/preset-template.yml', $this->getGlobalConfigFilePath());
            return $this->getGlobalConfigFilePath();
        }
        return null;
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
