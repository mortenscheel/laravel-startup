<?php

namespace MortenScheel\PhpDependencyInstaller\Transformers;

use MortenScheel\PhpDependencyInstaller\Concerns\ReportsErrors;

class AppendPhpMethodTransformer implements Transformer
{
    use ReportsErrors;
    /**
     * @var string
     */
    private $original;
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $append;

    /**
     * AppendPhpMethodTransformer constructor.
     * @param string $original
     * @param string $method
     * @param string $append
     */
    public function __construct(string $original, string $method, string $append)
    {
        $this->original = $original;
        $this->method = $method;
        $this->append = $append;
    }

    public function transform(): ?string
    {
        if ($method_match = $this->captureMethod()) {
            [$body, $body_offset] = $method_match[1];
            $indent = '';
            if (\preg_match("~%s([\t ]+)\S~mu", PHP_EOL, $body, $indent_match, \PREG_OFFSET_CAPTURE)) {
                $indent = $indent_match[1][0];
            }
            $offset = $body_offset + \mb_strlen($body);
            return \sprintf(
                '%s%s%s%s%s',
                \mb_substr($this->original, 0, $offset),
                PHP_EOL,
                $indent,
                $this->append,
                \mb_substr($this->original, $offset)
            );
        }
        return null;
    }

    private function captureMethod()
    {
        $method_capture = \sprintf(
            "~%s[\t ]+(?:public|private|protected)? function %s *\([^{]+{([^}]+)%s[\t ]*}~mu",
            PHP_EOL,
            $this->method,
            PHP_EOL
        );
        if (\preg_match($method_capture, $this->original, $method_match, \PREG_OFFSET_CAPTURE)) {
            return $method_match;
        }
        return null;
    }

    public function isTransformationRequired(): bool
    {
        if ($method_match = $this->captureMethod()) {
            $pattern = \sprintf('~%s$~u', \preg_quote($this->append, '~'));
            return !\preg_match($pattern, $method_match[1][0]);
        }
        return true; // Duplicate code might be intentional
    }
}
