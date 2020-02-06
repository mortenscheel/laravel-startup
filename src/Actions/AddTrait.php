<?php

namespace MortenScheel\LaravelBlitz\Actions;

class AddTrait extends Action
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
        parent::__construct();
        $this->class = $item['class'];
        $this->trait = $item['trait'];
    }

    public function getDescription(): string
    {
        return \sprintf('Add %s trait to %s', $this->trait, $this->class);
    }

    public function execute(): bool
    {
        $filename = $this->findClassFile($this->class);
        $classname = $this->getClassBaseName($this->class);
        $file = \file_get_contents($filename);
        $existing_use_pattern = \sprintf('~class %s[^{]*{[^{]*(\n\s*use )([^\n]+)~mu', $classname);
        $get_indent_pattern = \sprintf('~class %s[^{]*{\n([\t ]+)\S~mu', $classname);
        if (\preg_match($existing_use_pattern, $file, $match, \PREG_OFFSET_CAPTURE)) {
            [$use_start, $offset] = $match[1];
            if (\mb_stripos($match[2][0], $this->trait) !== false) {
                return true; // Trait already added
            }
            $before = \mb_substr($file, 0, $offset);
            $after = \mb_substr($file, $offset + \mb_strlen($use_start));
            $file = \sprintf('%s%s%s, %s', $before, $use_start, $this->trait, $after);
        } elseif (\preg_match($get_indent_pattern, $file, $match, \PREG_OFFSET_CAPTURE)) {
            [$indent, $offset] = $match[1];
            $before = \mb_substr($file, 0, $offset);
            $after = \mb_substr($file, $offset);
            $file = \sprintf("%s%suse %s;\n\n%s", $before, $indent, $this->trait, $after);
        } else {
            return false;
        }
        return \file_put_contents($filename, $file) !== false;
    }
}
