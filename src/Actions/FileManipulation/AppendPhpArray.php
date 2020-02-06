<?php

namespace MortenScheel\LaravelBlitz\Actions\FileManipulation;

use Illuminate\Console\Command;
use MortenScheel\LaravelBlitz\Actions\BaseAction;
use MortenScheel\LaravelBlitz\Rules\FileExistsRule;
use MortenScheel\LaravelBlitz\Rules\FileIsWriteableRule;

class AppendPhpArray extends BaseAction
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                new FileExistsRule(),
                new FileIsWriteableRule()
            ],
            'array' => [
                'required',
                'string'
            ],
            'value' => [
                'required',
            ]
        ];
    }

    public function handle(): bool
    {
        // Ensure dollar character is escaped
        $variable_name = \preg_replace("/(?<!\\\\)\\\$/", "\\\\\$$1", \trim($this->get('array')));
        $variable_capture_pattern = \sprintf('~%s\s*=\s*\[([^\]]+)]~mu', $variable_name);
        $file_path = $this->get('file');
        if (mb_stripos($file_path, 'app') === 0){
            $file_path = base_path($file_path);
        }
        $original = \File::get($file_path);
        $value = $this->get('value');
        if (\preg_match($variable_capture_pattern, $original, $variable_capture_match, \PREG_OFFSET_CAPTURE)) {
            [$array_content, $array_offset] = $variable_capture_match[1];
            // Return success if the value is already in the array
            if (\mb_stripos($array_content, $value) !== false) {
                return true;
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
                $result = "$before\n{$indent}{$value},$after";
                \File::put($file_path, $result);
                return true;
            }
        }
        return false;
    }
}
