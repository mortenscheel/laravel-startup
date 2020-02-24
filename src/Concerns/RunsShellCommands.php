<?php

namespace MortenScheel\PhpDependencyInstaller\Concerns;

use Symfony\Component\Process\Process;

trait RunsShellCommands
{
    use DetectsOperatingSystem;

    /** @var string */
    protected $process_output = '';

    protected static $executables = [];

    /**
     * @param array $command
     * @return bool
     */
    protected function shell(array $command): bool
    {
        $this->process_output = '';
        $process = self::createProcess($command);
        $process->start();
        foreach ($process as $type => $buffer) {
            $this->process_output .= \rtrim($buffer, "\n") . "\n";
        }
        return $process->getExitCode() === 0;
    }

    protected static function createProcess(array $command): Process
    {
        return new Process($command, \getcwd(), null, null);
    }

    protected static function createComposerCommand(array $command)
    {
        return array_merge([
            self::getExecutable('php'),
            '-n',
            self::getExecutable('composer')
        ], $command);
    }

    protected static function getShellOutput(array $command): ?string
    {
        $process = self::createProcess($command);
        if ($process->run() === 0) {
            return rtrim($process->getOutput());
        }
        return null;
    }

    protected static function getExecutable(string $executable): ?string
    {
        if (!\array_key_exists($executable, self::$executables)) {
            if (self::getOperatingSystem() === 'Windows') {
                $command = ['where'];
            } else {
                $command = ['command', '-v'];
            }
            $command[] = $executable;
            $path = \rtrim(self::getShellOutput($command));
            self::$executables[$executable] = $path;
        }
        return self::$executables[$executable];
    }
}
