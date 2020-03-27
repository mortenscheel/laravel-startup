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
        return $this->shell->createProcess($this->createCommand($this->command_line));
    }

    private function createCommand(string $command_string)
    {
        return \array_map(function ($part) {
            // Substitute ~ with absolute path
            if (\mb_stripos($part, '~') === 0) {
                return \getenv('HOME') . \mb_substr($part, 1);
            }
            return $part;
        }, \explode(' ', $this->command_line));
    }
}
