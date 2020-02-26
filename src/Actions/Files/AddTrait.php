<?php

namespace MortenScheel\PhpDependencyInstaller\Actions\Files;

use MortenScheel\PhpDependencyInstaller\Transformers\AddTraitTransformer;
use MortenScheel\PhpDependencyInstaller\Transformers\Transformer;

class AddTrait extends FileTransformerAction
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $trait;

    /**
     * AddTrait constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        $this->class = $item['class'];
        $this->trait = $item['trait'];
        parent::__construct($item);
    }

    public function getDescription(): string
    {
        return \sprintf('Add %s trait to %s', $this->trait, $this->class);
    }

    protected function getTransformer(string $original): ?Transformer
    {
        return new AddTraitTransformer(
            $original,
            self::getClassBaseName($this->class),
            $this->trait
        );
    }

    protected function getFilePath(): string
    {
        return self::findClassFile($this->class);
    }
}
