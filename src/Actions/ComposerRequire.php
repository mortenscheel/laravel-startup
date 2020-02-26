<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

use Symfony\Component\Process\Process;

class ComposerRequire extends Action implements AsyncAction
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
    /** @var bool  */
    public $skip_update = false;

    /**
     * InstallComposerPackage constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct($item);
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
        return $this->getProcess()->run() === 0;
    }

    public function isInstalled(): bool
    {
        return $this->shell->createComposerProcess([
            'show',
            '--quiet',
            $this->package]
        )->run() === 0;
    }

    public function getPackageWithVersion()
    {
        $name = $this->package;
        if ($this->version === null) {
            return $name;
        }
        return "{$name}={$this->version}";
    }

    public function getProcess(): Process
    {
        $package = $this->package;
        if ($this->version) {
            $package .= "={$this->version}";
        }
        $command = [
            'require',
            '--no-interaction',
            $package
        ];
        if ($this->dev) {
            $command[] = '--dev';
        }
        if ($this->skip_update) {
            $command[] = '--no-update';
        }
        return $this->shell->createComposerProcess($command);
    }
}
