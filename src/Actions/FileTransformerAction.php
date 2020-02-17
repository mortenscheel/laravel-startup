<?php

namespace MortenScheel\PhpDependencyInstaller\Actions;

use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

abstract class FileTransformerAction extends Action
{
    abstract protected function getTransformer(string $original): ?Transformer;
    abstract protected function getFilePath(): string;

    public function execute(): bool
    {
        $transformer = $this->getTransformer($this->filesystem->get($this->getFilePath()));
        if (!$transformer) {
            $this->error = 'Failed to create file transformer';
            return false;
        }
        if (!$transformer->isTransformationRequired()) {
            return true;
        }
        $updated = $transformer->transform();
        if (!$updated) {
            $this->error = 'Failed to transform file';
            return false;
        }
        $this->filesystem->put($this->getFilePath(), $updated);
        return true;
    }
}
