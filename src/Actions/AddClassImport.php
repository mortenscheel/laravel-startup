<?php

namespace MortenScheel\LaravelBlitz\Actions;

class AddClassImport extends Action
{
    private $class;
    private $import;

    public function __construct(array $item)
    {
        parent::__construct();
        $this->class = $item['class'];
        $this->import = $item['import'];
    }

    public function getDescription(): string
    {
        return \sprintf('Add %s as class import in %s', $this->import, $this->class);
    }

    public function execute(): bool
    {
        $filename = $this->findClassFile($this->class);
        $class_basename = $this->getClassBaseName($this->class);
        $file = $this->filesystem->get($filename);
        if (\mb_stripos($file, "use {$this->import}") !== false) {
            return true;
        }
        $pattern = \sprintf("~(\nclass %s)~mu", $class_basename);
        if (\preg_match($pattern, $file, $match, \PREG_OFFSET_CAPTURE)) {
            $offset = $match[1][1];
            $before = \mb_substr($file, 0, $offset);
            $after = \mb_substr($file, $offset);
            $file = \sprintf("%suse %s;\n%s", $before, $this->import, $after);
            $this->filesystem->put($filename, $file);
            return true;
        }
        return false;
    }

}
