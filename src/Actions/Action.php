<?php

namespace MortenScheel\LaravelBlitz\Actions;

use MortenScheel\LaravelBlitz\Concerns\ProcessRunner;
use MortenScheel\LaravelBlitz\Filesystem;
use MortenScheel\LaravelBlitz\Parser\ParserException;
use Tightenco\Collect\Contracts\Support\Arrayable;

abstract class Action implements ActionInterface, Arrayable
{
    use ProcessRunner;

    /** @var Filesystem */
    protected $filesystem;

    /** @var string|null */
    protected $error;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param array $settings
     * @return ActionInterface
     * @throws ParserException
     */
    public static function make(array $settings)
    {
        $action_name = array_get($settings, 'action');
        if (!$action_name) {
            throw new ParserException('Expected item to have action');
        }
        $class = "MortenScheel\LaravelBlitz\Actions\\$action_name";
        if (!\class_exists($class)) {
            throw new ParserException("Unknown Action: $action_name");
        }
        return new $class($settings);
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    public function toArray()
    {
        return [
            'description' => $this->getDescription()
        ];
    }

    /**
     * @param string $class
     * @return string|null
     */
    protected function findClassFile(string $class)
    {
        $key = \ltrim($class, "\\");
        $map = require \getcwd() . '/vendor/composer/autoload_classmap.php';
        return array_get($map, $key);
    }

    protected function getPhpExecutable()
    {
        return \rtrim(\shell_exec('which php'));
    }

    protected function getComposerExecutable()
    {
        return \rtrim(\shell_exec('which composer'));
    }

    protected function getClassBaseName(string $class)
    {
        $parts = \explode("\\", $class);
        return \array_pop($parts);
    }
}
