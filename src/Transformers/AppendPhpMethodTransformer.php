<?php

namespace MortenScheel\LaravelBlitz\Transformers;

use MortenScheel\LaravelBlitz\Concerns\ReportsErrors;

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
            if (\preg_match("~\n([\t ]+)\S~mu", $body, $indent_match, \PREG_OFFSET_CAPTURE)) {
                $indent = $indent_match[1][0];
            }
            $offset = $body_offset + \mb_strlen($body);
            return \sprintf(
                "%s\n%s%s%s",
                \mb_substr($this->original, 0, $offset),
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
            "~\n[\t ]+(?:public|private|protected)? function %s *\([^{]+{([^}]+)\n[\t ]*}~mu",
            $this->method
        );
        if (\preg_match($method_capture, $this->original, $method_match, \PREG_OFFSET_CAPTURE)) {
            return $method_match;
        }
    }

    public function isTransformationRequired(): bool
    {
        if ($method_match = $this->captureMethod()) {
            $pattern = \sprintf('~%s$~', $this->append);
            return !\preg_match($pattern, $method_match[1]);
        }
        return true; // Duplicate code might be intentional
    }
}
