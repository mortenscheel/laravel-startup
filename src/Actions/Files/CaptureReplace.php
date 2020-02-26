<?php

namespace MortenScheel\PhpDependencyInstaller\Actions\Files;

use MortenScheel\PhpDependencyInstaller\Filesystem;
use MortenScheel\PhpDependencyInstaller\Transformers\CaptureReplaceTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class CaptureReplace extends FileTransformerAction
{
    private $file;
    private $capture;
    private $replacement;

    /**
     * CaptureReplace constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        $this->file = $item['file'];
        $this->capture = $item['capture'];
        $this->replacement = $item['replacement'];
        parent::__construct($item);
    }

    public function getDescription(): string
    {
        return \sprintf('Replace %s with %s in %s', $this->capture, $this->replacement, $this->file);
    }

    protected function getTransformer(string $original): ?Transformer
    {
        return new CaptureReplaceTransformer(
            $original,
            $this->capture,
            $this->replacement
        );
    }

    protected function getFilePath(): string
    {
        return (new Filesystem)->getAbsolutePath($this->file);
    }
}
