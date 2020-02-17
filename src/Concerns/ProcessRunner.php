<?php

namespace MortenScheel\PhpDependencyInstaller\Concerns;

use Symfony\Component\Process\Process;

trait ProcessRunner
{
    /** @var string */
    protected $process_output = '';

    /**
     * Run command with streaming output
     * @param array $command
     * @return bool
     */
    protected function shell(array $command): bool
    {
        $this->process_output = '';
        $process = new Process($command, \getcwd(), null, null);
        $process->start();
        foreach ($process as $type => $buffer) {
            $this->process_output .= \rtrim($buffer, "\n") . "\n";
        }
        return $process->getExitCode() === 0;
    }

    protected function getPhpExecutable()
    {
        return \rtrim(\shell_exec('which php')); // todo: add windows support
    }

    protected function getComposerExecutable()
    {
        return \rtrim(\shell_exec('which composer'));
    }
}
