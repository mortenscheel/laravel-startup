<?php

namespace MortenScheel\LaravelBlitz\Parser;

use MortenScheel\LaravelBlitz\Actions\ActionCollection;
use MortenScheel\LaravelBlitz\Filesystem;

class ConfigParser implements ParserInterface
{
    /**
     * @return string
     * @throws ParserException
     */
    public function resolveConfigPath(): string
    {
        $filesystem = new Filesystem();
        $paths = [
            \getcwd() . '/blitz.yml',
            $filesystem->getGlobalConfigFilePath()
        ];
        foreach ($paths as $path) {
            if ($filesystem->exists($path)) {
                return $path;
            }
        }
        throw new ParserException('No config file found');
    }
    /**
     * @inheritDoc
     */
    public function getActions(): ActionCollection
    {
        $parser = new YamlConfigParser(\file_get_contents($this->resolveConfigPath()));
        return $parser->getActions();
    }
}
