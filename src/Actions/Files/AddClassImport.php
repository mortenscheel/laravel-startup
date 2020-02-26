<?php

namespace MortenScheel\PhpDependencyInstaller\Actions\Files;

use MortenScheel\PhpDependencyInstaller\Transformers\AddClassImportTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class AddClassImport extends FileTransformerAction
{
    private $class;
    private $import;

    public function __construct(array $item)
    {
        $this->class = $item['class'];
        $this->import = $item['import'];
        parent::__construct($item);
    }

    public function getDescription(): string
    {
        return \sprintf('Add %s as class import in %s', $this->import, $this->class);
    }

    protected function getTransformer(string $original): ?Transformer
    {
        $basename = self::getClassBaseName($this->class);
        return new AddClassImportTransformer($original, $basename, $this->import);
    }

    protected function getFilePath(): string
    {
        return self::findClassFile($this->class);
    }
}
