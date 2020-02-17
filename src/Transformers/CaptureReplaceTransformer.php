<?php


namespace MortenScheel\LaravelBlitz\Transformers;


use MortenScheel\LaravelBlitz\Concerns\ReportsErrors;

class CaptureReplaceTransformer implements Transformer
{
    use ReportsErrors;
    /**
     * @var string
     */
    private $original;
    /**
     * @var string
     */
    private $capture;
    /**
     * @var string
     */
    private $replacement;

    /**
     * CaptureReplaceTransformer constructor.
     * @param string $original
     * @param string $capture
     * @param string $replacement
     */
    public function __construct(string $original, string $capture, string $replacement)
    {
        $this->original = $original;
        $this->capture = $capture;
        $this->replacement = $replacement;
    }

    private function performCapture()
    {
        if (\preg_match($this->capture, $this->original, $match, \PREG_OFFSET_CAPTURE)) {
            return $match[1];
        }
    }

    public function transform(): ?string
    {
        if ($capture_match = $this->performCapture()) {
            [$captured, $offset] = $capture_match;
            return \sprintf(
                '%s%s%s',
                \mb_substr($this->original, 0, $offset),
                $this->replacement,
                \mb_substr($this->original, $offset + \mb_strlen($captured))
            );
        }
        $this->error = "{$this->capture} didn't capture anything";
        return false;
    }

    public function isTransformationRequired(): bool
    {
        if ($capture_match = $this->performCapture()) {
            return $capture_match[0] !== $this->replacement;
        }
        return true; // Should be safe to transform multiple times
    }
}
