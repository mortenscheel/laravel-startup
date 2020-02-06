<?php

namespace MortenScheel\LaravelBlitz\Actions;

class AppendPhpMethod extends Action
{
    private $class;
    private $method;
    private $append;

    /**
     * AppendPhpMethod constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct();
        $this->class = $item['class'];
        $this->method = $item['method'];
        $this->append = $item['append'];
    }

    public function getDescription(): string
    {
        return \sprintf('Append %s to the %s method in %s', $this->append, $this->method, $this->class);
    }

    public function execute(): bool
    {
        $filename = $this->findClassFile($this->class);
        $file = $this->filesystem->get($filename);
        $method_capture = \sprintf(
            "~\n[\t ]+(?:public|private|protected)? function %s *\([^{]+{([^}]+)\n[\t ]*}~mu",
            $this->method
        );
        if (\preg_match($method_capture, $file, $method_match, \PREG_OFFSET_CAPTURE)) {
            [$body, $body_offset] = $method_match[1];
            $indent = '';
            if (\preg_match("~\n([\t ]+)\S~mu", $body, $indent_match, \PREG_OFFSET_CAPTURE)) {
                $indent = $indent_match[1][0];
            }
            $offset = $body_offset + \mb_strlen($body);
            $before = \mb_substr($file, 0, $offset);
            $after = \mb_substr($file, $offset);
            $file = \sprintf("%s\n%s%s%s", $before, $indent, $this->append, $after);
            $this->filesystem->put($filename, $file);
            return true;
        }
        return false;
    }
}
