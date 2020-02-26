<?php

namespace MortenScheel\PhpDependencyInstaller\Actions\Files;

use MortenScheel\PhpDependencyInstaller\Actions\Action;

class CopyFile extends Action
{
    private $source;
    private $destination;

    public function __construct(array $item)
    {
        parent::__construct($item);
        $this->source = $item['source'];
        $this->destination = $item['destination'];
    }

    public function getDescription(): string
    {
        return \sprintf('Copy %s to %s', $this->source, $this->destination);
    }

    public function execute(): bool
    {
        $source_absolute = $this->filesystem->getAbsolutePath($this->source);
        $destination_absolute = $this->filesystem->getAbsolutePath($this->destination);
        if (!$this->filesystem->exists($source_absolute)) {
            $this->error = 'Source file not found';
            return false;
        }
        if (\mb_stripos($destination_absolute, \getcwd()) !== 0) {
            $this->error = 'Destination is outside the current folder';
            return false;
        }
        $destination_folder = \dirname($destination_absolute);
        if (!$this->filesystem->exists($destination_folder) &&
            !\mkdir($destination_folder, 0755, true) &&
            !\is_dir($destination_folder)) {
            $this->error = \sprintf('Directory "%s" was not created', $destination_folder);
            return false;
        }
        $this->filesystem->copy($source_absolute, $destination_absolute);
        return true;
    }
}
