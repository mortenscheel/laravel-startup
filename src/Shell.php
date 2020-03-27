<?php

namespace MortenScheel\PhpDependencyInstaller;

use Symfony\Component\Process\Process;

class Shell
{

    /** @var string */
    protected $process_output = '';

    protected $executables = [];

    /**
     * @param array $command
     * @return bool
     */
    public function execute(array $command): bool
    {
        $this->process_output = '';
        $process =  $this->createProcess($command);
        $process->start();
        foreach ($process as $type => $buffer) {
            $this->process_output .= \rtrim($buffer, "\n") . "\n";
        }
        return $process->getExitCode() === 0;
    }

    public function createProcess(array $command): Process
    {
        return new Process($command, \getcwd(), null, null);
    }

    public function flushOutput(): string
    {
        $output = $this->process_output;
        $this->process_output = '';
        return \rtrim($output);
    }

    public function createComposerProcess(array $command): Process
    {
        $composer = [
            $this->getExecutable('php'),
            '-n',
            $this->getExecutable('composer')
        ];
        if (OS::detect() === OS::WINDOWS) {
            // Temporary fix
            $composer = [$this->getExecutable('composer')];
        }
        return $this->createProcess(\array_merge($composer, $command));
    }

    public function createArtisanProcess(array $command): Process
    {
        return $this->createProcess(\array_merge([
            $this->getExecutable('php'),
            '-n',
            'artisan'
        ], $command));
    }

    protected function getExecutable(string $executable): ?string
    {
        if (!\array_key_exists($executable, $this->executables)) {
            if (OS::detect() === OS::WINDOWS) {
                $command = ['where'];
            } else {
                $command = ['command', '-v'];
            }
            $command[] = $executable;
            if ($this->execute($command)) {
                $path = $this->flushOutput();
                // Use first line in case of multi line output
                $path = \explode(\PHP_EOL, $path)[0];
            }
            $this->executables[$executable] = $path ?? null;
        }
        return $this->executables[$executable];
    }
}
