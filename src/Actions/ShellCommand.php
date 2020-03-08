<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

use Symfony\Component\Process\Process;

class ShellCommand extends Action implements AsyncAction
{
    /** @var string  */
    private $command_line;

    public function __construct(array $item)
    {
        parent::__construct($item);
        $this->command_line = array_get($item, 'command');
    }

    public function execute(): bool
    {
        return $this->getProcess()->run() === 0;
    }

    public function getDescription(): string
    {
        return "Run '{$this->command_line}'";
    }

    public function getProcess(): Process
    {
        return $this->shell->createProcess(\explode(' ', $this->command_line));
    }
}
