<?php

namespace MortenScheel\LaravelBlitz;

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

    public function hasBlitzConfig()
    {
        return $this->exists($this->getGlobalConfigFilePath());
    }

    private function getGlobalConfigFolder()
    {
        return \sprintf('%s/.config/blitz', \getenv('HOME'));
    }

    public function getGlobalConfigFilePath()
    {
        return \sprintf('%s/blitz.yml', $this->getGlobalConfigFolder());
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
            $this->copy(__DIR__ . '/../config/blitz.yml', $this->getGlobalConfigFilePath());
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
