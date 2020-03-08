<?php

namespace MortenScheel\PhpDependencyInstaller\Actions\Files;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Transformers\AppendFileTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class AppendFile extends FileTransformerAction
{
    private $file;
    private $append;

    public function __construct(array $item)
    {
        $this->file = $item['file'];
        $this->append = $item['append'];
        parent::__construct($item);
    }

    public function getDescription(): string
    {
        return \sprintf('Append %s to %s', $this->append, $this->file);
    }

    protected function getTransformer(string $original): ?Transformer
    {
        return new AppendFileTransformer(
            $original,
            $this->append
        );
    }

    protected function getFilePath(): string
    {
        return (new Filesystem)->getAbsolutePath($this->file);
    }
}
