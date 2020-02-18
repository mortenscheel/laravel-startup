<?php

namespace MortenScheel\PhpDependencyInstaller\Concerns;

use Symfony\Component\Process\Process;

trait RunsShellCommands
{
    use DetectsOperatingSystem;

    /** @var string */
    protected $process_output = '';

    protected $executables = [];

    /**
     * @param array $command
     * @return bool
     */
    protected function shell(array $command): bool
    {
        $this->process_output = '';
        $process = $this->createProcess($command);
        $process->start();
        foreach ($process as $type => $buffer) {
            $this->process_output .= \rtrim($buffer, "\n") . "\n";
        }
        return $process->getExitCode() === 0;
    }

    protected function createProcess(array $command): Process
    {
        return new Process($command, \getcwd(), null, null);
    }

    protected function getShellOutput(array $command): ?string
    {
        $process = $this->createProcess($command);
        if ($process->run() === 0) {
            return $process->getOutput();
        }
        return null;
    }

    protected function getExecutable(string $executable): ?string
    {
        if (!\array_key_exists($executable, $this->executables)) {
            if ($this->getOperatingSystem() === 'Windows') {
                $command = ['where'];
            } else {
                $command = ['command', '-v'];
            }
            $command[] = $executable;
            $path = \rtrim($this->getShellOutput($command));
            $this->executables[$executable] = $path;
        }
        return $this->executables[$executable];
    }
}
