<?php

namespace MortenScheel\LaravelBlitz\Concerns;

use Symfony\Component\Process\Process;

trait ProcessRunner
{
    /** @var string */
    protected $process_output = '';

    /**
     * Run command with streaming output
     * @param array $command
     * @param bool $silent
     * @return bool
     */
    protected function shell(array $command, bool $silent = false): bool
    {
        $this->process_output = '';
        $process = new Process($command, \getcwd(), null, null);
        $process->start();
        foreach ($process as $type => $buffer) {
            $line = \rtrim($buffer, "\n") . "\n";
            $this->process_output .= $line;
            if (!$silent) {
                \fwrite(\STDOUT, $line);
            }
        }
        return $process->getExitCode() === 0;
    }
}
