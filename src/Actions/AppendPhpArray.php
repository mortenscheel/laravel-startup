<?php

namespace MortenScheel\LaravelBlitz\Actions;

class AppendPhpArray extends Action
{
    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $array;
    /**
     * @var string
     */
    private $value;

    /**
     * AppendPhpArray constructor.
     * @param array $item
     */
    public function __construct(array $item)
    {
        parent::__construct();
        $this->file = $item['file'];
        $this->array = $item['array'];
        $this->value = $item['value'];
    }

    public function getDescription(): string
    {
        return "Append {$this->value} to the end of {$this->array} in {$this->file}";
    }

    public function execute(): bool
    {
        // Ensure dollar character is escaped
        $variable_name = \preg_replace("/(?<!\\\\)\\\$/", "\\\\\$$1", \trim($this->array));
        $variable_capture_pattern = \sprintf('~%s\s*=\s*\[([^\]]+)]~mu', $variable_name);
        $file_path = \getcwd() . '/' . $this->file;
        if (!$this->filesystem->exists($file_path)) {
            $this->error = 'File not found';
            return false;
        }
        $original = $this->filesystem->get($file_path);
        if (\preg_match($variable_capture_pattern, $original, $variable_capture_match, \PREG_OFFSET_CAPTURE)) {
            [$array_content, $array_offset] = $variable_capture_match[1];
            if (\mb_stripos($array_content, $this->value) !== false) {
                return true; // Already exists
            }
            // Capture final line and indentation
            if (\preg_match('~\n( *)(\S+)\s+$~u', $array_content, $final_line_match, \PREG_OFFSET_CAPTURE)) {
                $indent = $final_line_match[1][0];
                [$final_line, $final_line_offset] = $final_line_match[2];
                $offset = $array_offset + $final_line_offset + \mb_strlen($final_line);
                $before = \mb_substr($original, 0, $offset);
                if (!\preg_match('~,\s*$~', $before)) {
                    $before .= ',';
                }
                $after = \mb_substr($original, $offset);
                $result = "$before\n{$indent}{$this->value},$after";
                $this->filesystem->put($file_path, $result);
                return true;
            }
        }
        $this->error = "{$this->array} not found in {$this->file}";
        return false;
    }
}
