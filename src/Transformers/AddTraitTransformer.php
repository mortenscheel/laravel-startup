<?php

namespace MortenScheel\PhpDependencyInstaller\Transformers;

use MortenScheel\PhpDependencyInstaller\Concerns\ReportsErrors;

class AddTraitTransformer implements Transformer
{
    use ReportsErrors;
    /**
     * @var string
     */
    private $original;
    /**
     * @var string
     */
    private $basename;
    /**
     * @var string
     */
    private $trait;

    /**
     * AddTraitTransformer constructor.
     * @param string $original
     * @param string $basename
     * @param string $trait
     */
    public function __construct(string $original, string $basename, string $trait)
    {
        $this->original = $original;
        $this->basename = $basename;
        $this->trait = $trait;
    }

    public function transform(): ?string
    {
        $existing_use_pattern = \sprintf('~class %s[^{]*{[^{]*(%s\s*use )([^%s]+)~mu', $this->basename, \PHP_EOL, \PHP_EOL);
        $get_indent_pattern = \sprintf('~class %s[^{]*{%s([\t ]+)\S~mu', $this->basename, \PHP_EOL);
        if (\preg_match($existing_use_pattern, $this->original, $match, \PREG_OFFSET_CAPTURE)) {
            [$use_start, $offset] = $match[1];
            if (\mb_stripos($match[2][0], $this->trait) !== false) {
                return true; // Trait already added
            }
            return \sprintf(
                '%s%s%s, %s',
                \mb_substr($this->original, 0, $offset),
                $use_start,
                $this->trait,
                \mb_substr($this->original, $offset + \mb_strlen($use_start))
            );
        }
        if (\preg_match($get_indent_pattern, $this->original, $match, \PREG_OFFSET_CAPTURE)) {
            [$indent, $offset] = $match[1];
            $before = \mb_substr($this->original, 0, $offset);
            $after = \mb_substr($this->original, $offset);
            return \sprintf(
                '%s%suse %s;%s%s%s',
                $before,
                $indent,
                $this->trait,
                \PHP_EOL,
                \PHP_EOL,
                $after
            );
        }
        $this->error = 'Failed to add trait';
        return false;
    }

    public function isTransformationRequired(): bool
    {
        $pattern = \sprintf('~^\s*use .*%s.*$~mu', \preg_quote($this->trait, '~'));
        return !\preg_match($pattern, $this->original);
    }
}
