<?php

namespace MortenScheel\LaravelBlitz\Actions;

use MortenScheel\LaravelBlitz\Transformers\AddClassImportTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class AddClassImport extends FileTransformerAction
{
    private $class;
    private $import;

    public function __construct(array $item)
    {
        $this->class = $item['class'];
        $this->import = $item['import'];
        parent::__construct();
    }

    public function getDescription(): string
    {
        return \sprintf('Add %s as class import in %s', $this->import, $this->class);
    }

    protected function getTransformer(string $original): ?Transformer
    {
        $basename = $this->getClassBaseName($this->class);
        return new AddClassImportTransformer($original, $basename, $this->import);
    }

    protected function getFilePath(): string
    {
        return $this->findClassFile($this->class);
    }
}
