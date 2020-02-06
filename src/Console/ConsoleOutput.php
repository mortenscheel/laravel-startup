<?php
/** @noinspection PhpHierarchyChecksInspection */

namespace MortenScheel\LaravelBlitz\Console;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleOutput
 * @package MortenScheel\LaravelBlitz\Console
 * @mixin OutputInterface
 */
class ConsoleOutput
{
    /** @var OutputInterface */
    private $output;

    public function bind(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function __call($name, $arguments)
    {
        $output = $this->output ?: new class {
            // Placeholder object
            public function __call($name, $arguments)
            {
                return $this;
            }
        };
        return \call_user_func_array([$output, $name], $arguments);
    }
}
