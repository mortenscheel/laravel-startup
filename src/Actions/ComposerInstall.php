<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

class ComposerInstall extends Action
{
    /**
     * @var string
     */
    public $package;
    /**
     * @var string|null
     */
    public $version;
    /**
     * @var bool
     */
    public $dev;

    /**
     * InstallComposerPackage constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct();
        $this->package = $item['package'];
        $this->version = array_get($item, 'version');
        $this->dev = array_get($item, 'dev', false);
    }

    public function getDescription(): string
    {
        $description = "Install {$this->package}";
        if ($this->version) {
            if ($this->version === '*') {
                $description .= ' (latest)';
            } else {
                $description .= " ({$this->version})";
            }
        }
        if ($this->dev) {
            $description .= ' as dev dependency';
        }
        return $description;
    }

    public function execute(): bool
    {
        if ($this->isInstalled()) {
            return true;
        }
        $package = $this->package;
        if ($this->version) {
            $package .= "={$this->version}";
        }
        $command = [
            $this->getExecutable('php'),
            '-n',
            $this->getExecutable('composer'),
            'require',
            '--no-interaction',
            $package];
        if ($this->dev) {
            $command[] = '--dev';
        }
        if (!$this->shell($command)) {
            $this->error = "{$this->package} failed to install";
            return false;
        }
        return true;
    }

    public function isInstalled(): bool
    {
        return $this->shell([
            $this->getExecutable('php'),
            '-n',
            $this->getExecutable('composer'),
            'show',
            '--quiet',
            $this->package
        ]);
    }

    public function getPackageWithVersion()
    {
        $name = $this->package;
        if ($this->version === null) {
            return $name;
        }
        return "{$name}={$this->version}";
    }
}
