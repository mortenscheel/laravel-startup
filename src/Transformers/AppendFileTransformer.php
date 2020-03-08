<?php

namespace MortenScheel\PhpDependencyInstaller\Transformers;

class AppendFileTransformer implements Transformer
{
    /**
     * @var string
     */
    private $original;
    /**
     * @var string
     */
    private $append;

    /**
     * AppendFileTransformer constructor.
     * @param string $original
     * @param string $append
     */
    public function __construct(string $original, string $append)
    {
        $this->original = $original;
        $this->append = $append;
    }

    public function transform(): ?string
    {
        return $this->original .
            \PHP_EOL .
            $this->append;
    }

    public function isTransformationRequired(): bool
    {
        $regex = \sprintf('~%s~', \preg_quote($this->append, '~'));
        return !(bool) \preg_match($regex, $this->original);
    }

    public function getError(): ?string
    {
        return null;
    }
}
