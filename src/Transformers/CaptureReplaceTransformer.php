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

    public function transform(): ?string
    {
        if (\preg_match($this->capture, $this->original, $match, \PREG_OFFSET_CAPTURE)) {
            [$captured, $offset] = $match[1];
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
        return true; // Should be safe to transform multiple times
    }
}
