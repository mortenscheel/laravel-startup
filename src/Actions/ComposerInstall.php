<?php

namespace MortenScheel\LaravelBlitz\Actions;

class ComposerInstall extends Action
{
    /**
     * @var string
     */
    private $package;
    /**
     * @var string|null
     */
    private $version;
    /**
     * @var bool
     */
    private $dev;

    /**
     * InstallComposerPackage constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct();
        $this->package = $item['package'];
        $this->version = array_get($item, 'version', '*');
        $this->dev = array_get($item, 'dev', false);
    }

    public function getDescription(): string
    {
        $description = "Install {$this->package}";
        if ($this->version) {
            $description .= " ({$this->version})";
        }
        if ($this->dev) {
            $description .= ' as dev dependency';
        }
        return $description;
    }

    public function execute(): bool
    {
        if ($this->isInstalled()) {
            $this->error = "{$this->package} is already installed";
            return false;
        }
        $package = $this->package;
        if ($this->version) {
            $package .= "={$this->version}";
        }
        $command = [
            $this->getPhpExecutable(),
            '-n',
            $this->getComposerExecutable(),
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

    private function isInstalled(): bool
    {
        return $this->shell([
            $this->getPhpExecutable(),
            '-n',
            $this->getComposerExecutable(),
            'show',
            '--quiet',
            $this->package
        ], true);
    }
}
