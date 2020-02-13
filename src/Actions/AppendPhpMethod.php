<?php

namespace MortenScheel\LaravelBlitz\Actions;

use MortenScheel\LaravelBlitz\Transformers\AppendPhpMethodTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class AppendPhpMethod extends FileTransformerAction
{
    private $class;
    private $method;
    private $append;

    /**
     * AppendPhpMethod constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        $this->class = $item['class'];
        $this->method = $item['method'];
        $this->append = $item['append'];
        parent::__construct();
    }

    public function getDescription(): string
    {
        return \sprintf('Append %s to the %s method in %s', $this->append, $this->method, $this->class);
    }

    protected function getTransformer(string $original): ?Transformer
    {
        return new AppendPhpMethodTransformer(
            $original,
            $this->method,
            $this->append
        );
    }

    protected function getFilePath(): string
    {
        return $this->findClassFile($this->class);
    }
}
