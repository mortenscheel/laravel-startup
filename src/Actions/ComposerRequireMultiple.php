<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

use Symfony\Component\Process\Process;
use Tightenco\Collect\Support\Collection;

class ComposerRequireMultiple extends Action implements AsyncAction
{
    /** @var bool */
    public $dev;
    /** @var Collection */
    public $packages;
    /** @var bool  */
    public $skip_update = false;

    /**
     * InstallComposerPackage constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct($item);
        $this->packages = $item['packages'];
        $this->dev = array_get($item, 'dev', false);
    }

    public function getDescription(): string
    {
        return sprintf(
            'Install %s packages: %s',
            $this->packages->count() . ($this->dev ? ' development' : ''),
            $this->packages->join(', ', ' and ')
        );
    }

    public function execute(): bool
    {
        return $this->getProcess()->run() === 0;
    }

    public function getProcess(): Process
    {
        $command = array_merge([
            'require',
            '--no-interaction'
        ], $this->packages->toArray());
        if ($this->dev) {
            $command[] = '--dev';
        }
        if ($this->skip_update) {
            $command[] = '--no-update';
        }
        return $this->shell->createComposerProcess($command);
    }
}
