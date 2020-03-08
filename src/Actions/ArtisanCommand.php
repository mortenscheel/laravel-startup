<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

use Symfony\Component\Process\Process;

class ArtisanCommand extends Action implements AsyncAction
{
    /**
     * @var string
     */
    public $command;
    /**
     * @var array
     */
    public $arguments;

    /**
     * RunArtisanCommand constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct($item);
        $this->command = $item['command'];
        $this->arguments = array_get($item, 'args', []);
    }

    public function getDescription(): string
    {
        $description = "Run php artisan {$this->command}";
        if ($args = collect($this->parseArtisanArguments($this->arguments))->join(' ')) {
            $description .= " $args";
        }
        return $description;
    }

    protected function parseArtisanArguments(array $arguments): array
    {
        return collect($arguments)->map(static function ($value, $name) {
            if ($value !== true) {
                return "$name=$value";
            }
            return $name;
        })->toArray();
    }

    public function execute(): bool
    {
        return $this->getProcess()->run() === 0;
    }

    public function getProcess(): Process
    {
        $command = \array_merge([$this->command], $this->parseArtisanArguments($this->arguments));
        return $this->shell->createArtisanProcess($command);
    }
}
