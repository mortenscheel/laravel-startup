<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

class ArtisanCommand extends Action
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
        parent::__construct();
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
        if (!$this->filesystem->exists(\getcwd() . '/artisan')) {
            $this->error = 'Current folder is not a Laravel project';
            return false;
        }
        $command = [$this->getPhpExecutable(), '-n', 'artisan', $this->command];
        $arguments = $this->parseArtisanArguments($this->arguments);
        if (!$this->shell(\array_merge($command, $arguments))) {
            $this->error = 'Artisan command failed';
            return false;
        }
        return true;
    }
}
