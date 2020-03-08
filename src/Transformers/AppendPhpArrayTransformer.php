<?php

namespace MortenScheel\PhpDependencyInstaller\Transformers;

use MortenScheel\PhpDependencyInstaller\Concerns\ReportsErrors;

class AppendPhpArrayTransformer implements Transformer
{
    use ReportsErrors;
    /**
     * @var string
     */
    private $original;
    /**
     * @var string
     */
    private $variable;
    /**
     * @var string
     */
    private $value;

    /**
     * AppendPhpArrayTransformer constructor.
     * @param string $original
     * @param string $variable
     * @param string $value
     */
    public function __construct(string $original, string $variable, string $value)
    {
        $this->original = $original;
        $this->variable = $variable;
        $this->value = $value;
    }

    public function transform(): ?string
    {
        if ($variable_capture_match = $this->captureVariableBody()) {
            [$array_content, $array_offset] = $variable_capture_match[1];
            // Capture final line and indentation
            if (\preg_match('~' . \PHP_EOL . '( *)(\S+)?\s+$~u', $array_content, $final_line_match, \PREG_OFFSET_CAPTURE)) {
                $indent = $final_line_match[1][0];
                if (isset($final_line_match[2])) {
                    [$final_line, $final_line_offset] = $final_line_match[2];
                } else {
                    $final_line = '';
                    $final_line_offset = 0;
                }
                $offset = $array_offset + $final_line_offset + \mb_strlen($final_line);
                $before = \mb_substr($this->original, 0, $offset);
                if ($final_line !== '' && !\preg_match('~,\s*$~', $before)) {
                    $before .= ',';
                }
                $after = \mb_substr($this->original, $offset);
                return \sprintf('%s%s%s%s,%s', $before, \PHP_EOL, $indent, $this->value, $after);
            }
            if (!\preg_match('~\\n~', $array_content)) {
                // Single line array
                $offset = $array_offset + \mb_strlen($array_content);
                $before = \mb_substr($this->original, 0, $offset);
                $after = \mb_substr($this->original, $offset);
                return \sprintf('%s, %s%s', $before, $this->value, $after);
            }
        }
        $this->error = "{$this->variable} not found";
        return false;
    }

    /**
     * @return array|null
     */
    private function captureVariableBody()
    {
        $variable_capture_pattern = \sprintf('~%s\s*=\s*\[([^\]]*)]~mu', \preg_quote($this->variable, '~'));
        \preg_match($variable_capture_pattern, $this->original, $match, \PREG_OFFSET_CAPTURE);
        return $match;
    }

    public function isTransformationRequired(): bool
    {
        if ($variable_capture_match = $this->captureVariableBody()) {
            return \mb_stripos($variable_capture_match[1][0], $this->value) === false;
        }
        return true;
    }
}
