<?php

namespace MortenScheel\LaravelBlitz\Concerns;

use Illuminate\Contracts\Container\BindingResolutionException;
use MortenScheel\LaravelBlitz\Console\ConsoleOutput;
use Symfony\Component\Process\Process;

trait RunsProcessCommands
{
    /**
     * Run command with streaming output
     * @param array $command
     * @return int
     * @noinspection PhpUnusedParameterInspection
     */
    protected function runProcess(array $command): int
    {
        $process = new Process($command);
        $process->setTty(config('laravel-tools.tty_commands'));
        $output = app(ConsoleOutput::class);
        return $process->setTimeout(null)->run(static function ($type, $buffer) use ($output) {
            $output->write($buffer);
        }) === 0;
    }

    protected function runArtisanCommand(string $command, array $parameters = []): bool
    {
        $args = ['php', 'artisan', $command];
        $parameters = collect($parameters)->map(static function ($value, $name) {
            if ($value !== true) {
                return "$name=$value";
            }
            return $name;
        })->toArray();
        return $this->runProcess(\array_merge($args, $parameters));
    }
}
