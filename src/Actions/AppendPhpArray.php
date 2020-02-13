<?php

namespace MortenScheel\LaravelBlitz\Actions;

use MortenScheel\LaravelBlitz\Filesystem;
use MortenScheel\LaravelBlitz\Transformers\AppendPhpArrayTransformer;
use MortenScheel\LaravelBlitz\Transformers\Transformer;

class AppendPhpArray extends FileTransformerAction
{
    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $array;
    /**
     * @var string
     */
    private $value;

    /**
     * AppendPhpArray constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        $this->file = $item['file'];
        $this->array = $item['array'];
        $this->value = $item['value'];
        parent::__construct();
    }

    public function getDescription(): string
    {
        return "Append {$this->value} to the end of {$this->array} in {$this->file}";
    }

    protected function getTransformer(string $original): ?Transformer
    {
        return new AppendPhpArrayTransformer(
            $original,
            $this->array,
            $this->value
        );
    }

    protected function getFilePath(): string
    {
        return $this->filesystem->getAbsolutePath($this->file);
    }
}
